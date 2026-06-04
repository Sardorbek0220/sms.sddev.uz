<?php

namespace App\Services;

use App\Call;
use App\Support\PhoneNumber;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CallAnalyticsService
{
    public function buildDashboard(string $fromDate, string $toDate, ?int $gateway = null): array
    {
        $from = Carbon::parse($fromDate)->startOfDay();
        $to = Carbon::parse($toDate)->endOfDay();
        $searchEnd = Carbon::now()->greaterThan($to->copy()->addDays(7))
            ? $to->copy()->addDays(7)
            : Carbon::now();

        $rangeCalls = Call::with('operator')
            ->whereBetween('created_at', [$from->toDateTimeString(), $to->toDateTimeString()])
            ->when($gateway, function ($q) use ($gateway) { return $q->where('gateway', $gateway); })
            ->orderBy('created_at')
            ->get();

        $missedCalls = $rangeCalls->filter(function (Call $call) {
            return $call->direction === 'inbound' && (int) ($call->dialog_duration ?? 0) <= 0;
        })->values();

        $phones = $missedCalls->map(function (Call $call) {
            return $this->normalizePhone($call->client_telephone);
        })->filter()->unique()->values()->all();

        $futureAnsweredCalls = Call::with('operator')
            ->where('dialog_duration', '>', 0)
            ->whereBetween('created_at', [$from->toDateTimeString(), $searchEnd->toDateTimeString()])
            ->when($gateway, function ($q) use ($gateway) { return $q->where('gateway', $gateway); })
            ->orderBy('created_at')
            ->get()
            ->filter(function (Call $call) use ($phones) {
                return in_array($this->normalizePhone($call->client_telephone), $phones, true);
            })
            ->values();

        $answeredByPhone = $futureAnsweredCalls
            ->groupBy(function (Call $call) {
                return $this->normalizePhone($call->client_telephone);
            })
            ->map(function (Collection $calls) {
                return $calls->sortBy('created_at')->values();
            });

        $workingIntervals = $this->loadWorkingIntervals($from->copy()->subDay(), $searchEnd->copy()->addDay());
        $departmentIntervals = $workingIntervals['department'];
        $operatorIntervals = $workingIntervals['operators'];

        $resolvedMissedCalls = $missedCalls->map(function (Call $missedCall) use ($answeredByPhone, $departmentIntervals, $operatorIntervals) {
            $normalizedPhone = $this->normalizePhone($missedCall->client_telephone);
            $missedTimestamp = $this->resolveCallTimestamp($missedCall);
            $answeredCall = $this->findFirstAnsweredCallAfter(
                $answeredByPhone->get($normalizedPhone, collect()),
                $missedTimestamp
            );

            $record = [
                'missed_call_id' => (int) $missedCall->id,
                'missed_uuid' => (string) $missedCall->uuid,
                'phone' => (string) $missedCall->client_telephone,
                'missed_at' => (string) $missedCall->created_at,
                'missed_operator_id' => $missedCall->operator_id ? (int) $missedCall->operator_id : null,
                'missed_operator_name' => (string) optional($missedCall->operator)->name,
                'missed_operator_phone' => (string) optional($missedCall->operator)->phone,
                'missed_gateway' => (string) $missedCall->gateway,
                'is_resolved' => false,
                'closure_type' => null,
                'resolved_at' => null,
                'resolved_call_id' => null,
                'resolved_operator_id' => null,
                'resolved_operator_name' => null,
                'resolved_operator_phone' => null,
                'raw_delay_seconds' => null,
                'working_delay_seconds' => null,
                'operator_working_delay_seconds' => null,
                'raw_delay_human' => null,
                'working_delay_human' => null,
                'talk_seconds' => 0,
            ];

            if (!$answeredCall || $missedTimestamp === null) {
                return $record;
            }

            $answeredTimestamp = $this->resolveCallTimestamp($answeredCall);

            if ($answeredTimestamp === null || $answeredTimestamp < $missedTimestamp) {
                return $record;
            }

            $resolvedOperatorPhone = optional($answeredCall->operator)->phone;

            $record['is_resolved'] = true;
            $record['closure_type'] = $answeredCall->direction === 'outbound'
                ? 'Оператор перезвонил'
                : 'Клиент снова дозвонился';
            $record['resolved_at'] = (string) $answeredCall->created_at;
            $record['resolved_call_id'] = (int) $answeredCall->id;
            $record['resolved_operator_id'] = $answeredCall->operator_id ? (int) $answeredCall->operator_id : null;
            $record['resolved_operator_name'] = (string) optional($answeredCall->operator)->name;
            $record['resolved_operator_phone'] = (string) $resolvedOperatorPhone;
            $record['raw_delay_seconds'] = max($answeredTimestamp - $missedTimestamp, 0);
            $record['working_delay_seconds'] = $this->calculateWorkingSeconds(
                $departmentIntervals,
                $missedTimestamp,
                $answeredTimestamp
            );
            $record['operator_working_delay_seconds'] = $resolvedOperatorPhone !== null
                ? $this->calculateWorkingSeconds(
                    $operatorIntervals[(string) $resolvedOperatorPhone] ?? [],
                    $missedTimestamp,
                    $answeredTimestamp
                )
                : null;
            $record['talk_seconds'] = (int) ($answeredCall->dialog_duration ?? 0);
            $record['raw_delay_human'] = $this->formatDuration((int) $record['raw_delay_seconds']);
            $record['working_delay_human'] = $this->formatDuration((int) $record['working_delay_seconds']);

            return $record;
        })->values();

        $talkedCalls = $rangeCalls->filter(function (Call $call) {
            return (int) ($call->dialog_duration ?? 0) > 0;
        })->values();

        return [
            'period' => [
                'from_date' => $from->format('Y-m-d'),
                'to_date' => $to->format('Y-m-d'),
                'search_end' => $searchEnd->format('Y-m-d H:i:s'),
            ],
            'summary' => $this->buildSummary($rangeCalls, $talkedCalls, $resolvedMissedCalls),
            'operator_rows' => $this->buildOperatorRows($rangeCalls, $resolvedMissedCalls),
            'recent_missed_rows' => $resolvedMissedCalls->sortByDesc('missed_at')->take(40)->values()->all(),
            'charts' => [
                'hours' => $this->buildHourLoad($rangeCalls),
                'weekdays' => $this->buildWeekdayLoad($rangeCalls),
                'monthdays' => $this->buildMonthdayLoad($rangeCalls),
                'callback_buckets' => $this->buildCallbackBuckets($resolvedMissedCalls),
            ],
        ];
    }

    protected function buildSummary(Collection $rangeCalls, Collection $talkedCalls, Collection $resolvedMissedCalls): array
    {
        $missedTotal = $resolvedMissedCalls->count();
        $resolvedTotal = $resolvedMissedCalls->where('is_resolved', true)->count();
        $unresolvedTotal = max($missedTotal - $resolvedTotal, 0);
        $resolvedCollection = $resolvedMissedCalls->where('is_resolved', true)->values();
        $avgWorkingDelay = $resolvedCollection->avg('working_delay_seconds') ?: 0;
        $avgRawDelay = $resolvedCollection->avg('raw_delay_seconds') ?: 0;
        $avgTalkSeconds = $talkedCalls->avg(function (Call $call) {
            return (int) ($call->dialog_duration ?? 0);
        }) ?: 0;
        $totalTalkSeconds = $talkedCalls->sum(function (Call $call) {
            return (int) ($call->dialog_duration ?? 0);
        });

        return [
            'total_calls' => $rangeCalls->count(),
            'talked_calls' => $talkedCalls->count(),
            'missed_inbound_calls' => $missedTotal,
            'resolved_missed_calls' => $resolvedTotal,
            'unresolved_missed_calls' => $unresolvedTotal,
            'resolution_rate_percent' => $missedTotal > 0 ? round(($resolvedTotal / $missedTotal) * 100, 1) : 0,
            'avg_working_callback_seconds' => (int) round($avgWorkingDelay),
            'avg_working_callback_human' => $this->formatDuration((int) round($avgWorkingDelay)),
            'avg_raw_callback_seconds' => (int) round($avgRawDelay),
            'avg_raw_callback_human' => $this->formatDuration((int) round($avgRawDelay)),
            'avg_talk_seconds' => (int) round($avgTalkSeconds),
            'avg_talk_human' => $this->formatDuration((int) round($avgTalkSeconds)),
            'total_talk_seconds' => (int) $totalTalkSeconds,
            'total_talk_human' => $this->formatDuration((int) $totalTalkSeconds),
            'sla_5_percent' => $this->percentWithinSla($resolvedCollection, 5 * 60),
            'sla_15_percent' => $this->percentWithinSla($resolvedCollection, 15 * 60),
            'sla_30_percent' => $this->percentWithinSla($resolvedCollection, 30 * 60),
        ];
    }

    protected function buildOperatorRows(Collection $rangeCalls, Collection $resolvedMissedCalls): array
    {
        $operatorRows = [];

        foreach ($rangeCalls->groupBy('operator_id') as $operatorId => $calls) {
            $operator = optional($calls->first())->operator;
            $talkedCalls = $calls->filter(function (Call $call) {
                return (int) ($call->dialog_duration ?? 0) > 0;
            });
            $missedReceived = $calls->filter(function (Call $call) {
                return $call->direction === 'inbound' && (int) ($call->dialog_duration ?? 0) <= 0;
            })->count();
            $resolvedByOperator = $resolvedMissedCalls->filter(function (array $row) use ($operatorId) {
                return (int) ($row['resolved_operator_id'] ?? 0) === (int) $operatorId;
            })->values();
            $avgCallbackSeconds = $resolvedByOperator->avg(function (array $row) {
                return $row['operator_working_delay_seconds'] ?? $row['working_delay_seconds'];
            }) ?: 0;

            $operatorRows[] = [
                'operator_id' => $operatorId ? (int) $operatorId : null,
                'operator_name' => $operator ? (string) $operator->name : 'Без оператора',
                'operator_phone' => $operator ? (string) $operator->phone : '',
                'total_calls' => $calls->count(),
                'talked_calls' => $talkedCalls->count(),
                'missed_received' => $missedReceived,
                'resolved_missed' => $resolvedByOperator->count(),
                'avg_callback_seconds' => (int) round($avgCallbackSeconds),
                'avg_callback_human' => $this->formatDuration((int) round($avgCallbackSeconds)),
                'avg_talk_seconds' => (int) round($talkedCalls->avg(function (Call $call) {
                    return (int) ($call->dialog_duration ?? 0);
                }) ?: 0),
                'avg_talk_human' => $this->formatDuration((int) round($talkedCalls->avg(function (Call $call) {
                    return (int) ($call->dialog_duration ?? 0);
                }) ?: 0)),
                'total_talk_human' => $this->formatDuration((int) $talkedCalls->sum(function (Call $call) {
                    return (int) ($call->dialog_duration ?? 0);
                })),
            ];
        }

        usort($operatorRows, function (array $left, array $right) {
            return [$right['resolved_missed'], $right['total_calls']] <=> [$left['resolved_missed'], $left['total_calls']];
        });

        return $operatorRows;
    }

    protected function buildHourLoad(Collection $calls): array
    {
        $rows = [];

        for ($hour = 0; $hour < 24; $hour++) {
            $rows[$hour] = [
                'label' => sprintf('%02d:00', $hour),
                'total' => 0,
                'talked' => 0,
                'missed' => 0,
            ];
        }

        foreach ($calls as $call) {
            $timestamp = $this->resolveCallTimestamp($call);
            if ($timestamp === null) {
                continue;
            }
            $hour = (int) date('G', $timestamp);
            $rows[$hour]['total']++;
            if ((int) ($call->dialog_duration ?? 0) > 0) {
                $rows[$hour]['talked']++;
            }
            if ($call->direction === 'inbound' && (int) ($call->dialog_duration ?? 0) <= 0) {
                $rows[$hour]['missed']++;
            }
        }

        return [
            'labels' => array_column($rows, 'label'),
            'total' => array_column($rows, 'total'),
            'talked' => array_column($rows, 'talked'),
            'missed' => array_column($rows, 'missed'),
        ];
    }

    protected function buildWeekdayLoad(Collection $calls): array
    {
        $labels = [
            1 => 'Пн',
            2 => 'Вт',
            3 => 'Ср',
            4 => 'Чт',
            5 => 'Пт',
            6 => 'Сб',
            7 => 'Вс',
        ];

        $rows = [];
        foreach ($labels as $day => $label) {
            $rows[$day] = [
                'label' => $label,
                'total' => 0,
                'talked' => 0,
                'missed' => 0,
            ];
        }

        foreach ($calls as $call) {
            $timestamp = $this->resolveCallTimestamp($call);
            if ($timestamp === null) {
                continue;
            }
            $day = (int) date('N', $timestamp);
            $rows[$day]['total']++;
            if ((int) ($call->dialog_duration ?? 0) > 0) {
                $rows[$day]['talked']++;
            }
            if ($call->direction === 'inbound' && (int) ($call->dialog_duration ?? 0) <= 0) {
                $rows[$day]['missed']++;
            }
        }

        return [
            'labels' => array_column($rows, 'label'),
            'total' => array_column($rows, 'total'),
            'talked' => array_column($rows, 'talked'),
            'missed' => array_column($rows, 'missed'),
        ];
    }

    protected function buildMonthdayLoad(Collection $calls): array
    {
        $rows = [];
        for ($day = 1; $day <= 31; $day++) {
            $rows[$day] = [
                'label' => (string) $day,
                'total' => 0,
                'talked' => 0,
                'missed' => 0,
            ];
        }

        foreach ($calls as $call) {
            $timestamp = $this->resolveCallTimestamp($call);
            if ($timestamp === null) {
                continue;
            }
            $day = (int) date('j', $timestamp);
            $rows[$day]['total']++;
            if ((int) ($call->dialog_duration ?? 0) > 0) {
                $rows[$day]['talked']++;
            }
            if ($call->direction === 'inbound' && (int) ($call->dialog_duration ?? 0) <= 0) {
                $rows[$day]['missed']++;
            }
        }

        return [
            'labels' => array_column($rows, 'label'),
            'total' => array_column($rows, 'total'),
            'talked' => array_column($rows, 'talked'),
            'missed' => array_column($rows, 'missed'),
        ];
    }

    protected function buildCallbackBuckets(Collection $resolvedMissedCalls): array
    {
        $buckets = [
            'До 5 мин' => 0,
            '5-15 мин' => 0,
            '15-30 мин' => 0,
            '30-60 мин' => 0,
            'Больше часа' => 0,
        ];

        foreach ($resolvedMissedCalls->where('is_resolved', true) as $row) {
            $seconds = (int) ($row['working_delay_seconds'] ?? 0);

            if ($seconds <= 5 * 60) {
                $buckets['До 5 мин']++;
            } elseif ($seconds <= 15 * 60) {
                $buckets['5-15 мин']++;
            } elseif ($seconds <= 30 * 60) {
                $buckets['15-30 мин']++;
            } elseif ($seconds <= 60 * 60) {
                $buckets['30-60 мин']++;
            } else {
                $buckets['Больше часа']++;
            }
        }

        return [
            'labels' => array_keys($buckets),
            'values' => array_values($buckets),
        ];
    }

    protected function loadWorkingIntervals(Carbon $from, Carbon $to): array
    {
        $rows = DB::table('operator_times')
            ->select('uid', 'timestamp_reg', 'timestamp_unreg')
            ->where('timestamp_reg', '<=', $to->timestamp)
            ->where(function ($query) use ($from) {
                $query->whereNull('timestamp_unreg')
                    ->orWhere('timestamp_unreg', 0)
                    ->orWhere('timestamp_unreg', '>=', $from->timestamp);
            })
            ->orderBy('timestamp_reg')
            ->get();

        $byOperator = [];
        $department = [];

        foreach ($rows as $row) {
            $start = max((int) $row->timestamp_reg, $from->timestamp);
            $maxSessionEnd = ((int) $row->timestamp_reg) + (12 * 3600);
            $end = (int) ($row->timestamp_unreg ?: $to->timestamp);
            $end = min($end, $maxSessionEnd);
            $end = min($end, $to->timestamp);

            if ($end <= $start) {
                continue;
            }

            $interval = [$start, $end];
            $uid = (string) $row->uid;
            $byOperator[$uid][] = $interval;
            $department[] = $interval;
        }

        foreach ($byOperator as $uid => $intervals) {
            $byOperator[$uid] = $this->mergeIntervals($intervals);
        }

        return [
            'department' => $this->mergeIntervals($department),
            'operators' => $byOperator,
        ];
    }

    protected function mergeIntervals(array $intervals): array
    {
        if (empty($intervals)) {
            return [];
        }

        usort($intervals, function (array $left, array $right) {
            return $left[0] <=> $right[0];
        });

        $merged = [$intervals[0]];

        foreach ($intervals as $interval) {
            $lastIndex = count($merged) - 1;
            if ($interval[0] <= $merged[$lastIndex][1]) {
                $merged[$lastIndex][1] = max($merged[$lastIndex][1], $interval[1]);
            } else {
                $merged[] = $interval;
            }
        }

        return $merged;
    }

    protected function calculateWorkingSeconds(array $intervals, int $fromTimestamp, int $toTimestamp): int
    {
        if ($toTimestamp <= $fromTimestamp) {
            return 0;
        }

        $seconds = 0;

        foreach ($intervals as $interval) {
            $start = max($fromTimestamp, (int) $interval[0]);
            $end = min($toTimestamp, (int) $interval[1]);

            if ($end > $start) {
                $seconds += ($end - $start);
            }
        }

        return $seconds;
    }

    protected function findFirstAnsweredCallAfter(Collection $answeredCalls, ?int $missedTimestamp)
    {
        if ($missedTimestamp === null) {
            return null;
        }

        foreach ($answeredCalls as $call) {
            $timestamp = $this->resolveCallTimestamp($call);
            if ($timestamp !== null && $timestamp >= $missedTimestamp) {
                return $call;
            }
        }

        return null;
    }

    protected function resolveCallTimestamp(Call $call): ?int
    {
        if (!empty($call->date)) {
            return (int) $call->date;
        }

        if (!empty($call->created_at)) {
            return Carbon::parse($call->created_at)->timestamp;
        }

        return null;
    }

    protected function percentWithinSla(Collection $rows, int $seconds): float
    {
        $count = $rows->count();
        if ($count === 0) {
            return 0;
        }

        $matched = $rows->filter(function (array $row) use ($seconds) {
            return (int) ($row['working_delay_seconds'] ?? 0) <= $seconds;
        })->count();

        return round(($matched / $count) * 100, 1);
    }

    protected function normalizePhone($value): ?string
    {
        return PhoneNumber::canonicalUzDigits($value);
    }

    protected function formatDuration(int $seconds): string
    {
        if ($seconds <= 0) {
            return '0:00';
        }

        $hours = (int) floor($seconds / 3600);
        $minutes = (int) floor(($seconds % 3600) / 60);
        $remainingSeconds = $seconds % 60;

        if ($hours > 0) {
            return sprintf('%d:%02d:%02d', $hours, $minutes, $remainingSeconds);
        }

        return sprintf('%d:%02d', $minutes, $remainingSeconds);
    }
}
