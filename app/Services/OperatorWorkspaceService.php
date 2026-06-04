<?php

namespace App\Services;

use App\Call;
use App\Support\PhoneNumber;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class OperatorWorkspaceService
{
    private const AUTO_POPUP_WINDOW_MINUTES = 5;

    protected $bitrixSurveyService;

    public function __construct(BitrixSurveyService $bitrixSurveyService)
    {
        $this->bitrixSurveyService = $bitrixSurveyService;
    }

    public function buildForUser(User $user): array
    {
        $operator = $user->operator;

        if (!$operator) {
            return [
                'operator' => null,
                'generated_at' => now()->toDateTimeString(),
                'stats' => $this->emptyStats(),
                'popup_call' => null,
                'recent_calls' => [],
                'recent_surveys' => [],
            ];
        }

        $today = Carbon::today();
        $fromDate = $today->format('Y-m-d');
        $toDate = $today->format('Y-m-d');

        $todayCalls = Call::with('operator')
            ->where('operator_id', $operator->id)
            ->whereBetween('created_at', [$fromDate . ' 00:00:00', $toDate . ' 23:59:59'])
            ->orderByDesc('created_at')
            ->get();

        $todayCalls = $this->bitrixSurveyService->attachToCalls($todayCalls, $fromDate, $toDate);

        $recentCallsRangeStart = Carbon::now()->subDays(7)->format('Y-m-d');
        $recentCalls = Call::with('operator')
            ->where('operator_id', $operator->id)
            ->whereBetween('created_at', [$recentCallsRangeStart . ' 00:00:00', $toDate . ' 23:59:59'])
            ->orderByDesc('created_at')
            ->limit(12)
            ->get();

        $recentCalls = $this->bitrixSurveyService->attachToCalls($recentCalls, $recentCallsRangeStart, $toDate);

        return [
            'operator' => [
                'id' => (int) $operator->id,
                'name' => (string) $operator->name,
                'phone' => (string) $operator->phone,
            ],
            'generated_at' => now()->toDateTimeString(),
            'stats' => $this->buildStats($todayCalls),
            'popup_call' => $this->resolvePopupCall($operator, $recentCalls),
            'recent_calls' => $recentCalls->map(function (Call $call) {
                return $this->formatCallRow($call);
            })->values()->all(),
            'recent_surveys' => $recentCalls
                ->filter(function (Call $call) {
                    return !empty($call->bitrixSurvey);
                })
                ->map(function (Call $call) {
                    return $this->formatSurveyRow($call);
                })
                ->values()
                ->take(8)
                ->all(),
        ];
    }

    protected function resolvePopupCall($operator, Collection $recentCalls): ?array
    {
        $liveCall = $this->findLatestInboundLiveCall($operator->phone);

        if ($liveCall) {
            $callRecord = Call::with('operator')->where('uuid', $liveCall->uuid)->first();

            if ($callRecord) {
                $date = Carbon::parse($callRecord->created_at)->format('Y-m-d');
                $callRecord = $this->bitrixSurveyService
                    ->attachToCalls(collect([$callRecord]), $date, $date)
                    ->first();

                if (empty($callRecord->bitrixSurvey)) {
                    return $this->formatPopupCall($callRecord, $liveCall);
                }
            } elseif ($this->shouldKeepLivePopupVisible($liveCall)) {
                return $this->formatLiveOnlyPopup($liveCall, $operator);
            }
        }

        $pendingCall = $recentCalls->first(function (Call $call) {
            return (int) ($call->dialog_duration ?? 0) > 0 && empty($call->bitrixSurvey);
        });

        if ($pendingCall) {
            return $this->formatPopupCall($pendingCall);
        }

        return null;
    }

    protected function findLatestInboundLiveCall(string $operatorPhone)
    {
        if ($operatorPhone === '') {
            return null;
        }

        return DB::table('all_calls')
            ->select([
                'id',
                'uuid',
                'caller_id_number',
                'destination_number',
                'accountcode',
                'gateway',
                'start_stamp',
                'end_stamp',
                'duration',
                'user_talk_time',
            ])
            ->where('destination_number', $operatorPhone)
            ->where('accountcode', 'inbound')
            ->where('start_stamp', '>=', Carbon::now()->subHours(6)->timestamp)
            ->orderByDesc('start_stamp')
            ->first();
    }

    protected function shouldKeepLivePopupVisible($liveCall): bool
    {
        $startStamp = (int) ($liveCall->start_stamp ?? 0);
        $endStamp = (int) ($liveCall->end_stamp ?? 0);
        $talkTime = (int) ($liveCall->user_talk_time ?? 0);

        if ($startStamp <= 0) {
            return false;
        }

        if ($endStamp <= 0) {
            return true;
        }

        return $talkTime > 0 && $endStamp >= Carbon::now()->subMinutes(45)->timestamp;
    }

    protected function buildStats(Collection $todayCalls): array
    {
        $totalCalls = $todayCalls->count();
        $inboundCalls = $todayCalls->where('direction', 'inbound')->count();
        $outboundCalls = $todayCalls->where('direction', 'outbound')->count();
        $surveyableCalls = $todayCalls->filter(function (Call $call) {
            return (int) ($call->dialog_duration ?? 0) > 0;
        });
        $surveyedCalls = $surveyableCalls->filter(function (Call $call) {
            return !empty($call->bitrixSurvey);
        })->count();
        $pendingSurveys = max($surveyableCalls->count() - $surveyedCalls, 0);
        $talkedCalls = $surveyableCalls;
        $avgTalkSeconds = $talkedCalls->count() > 0
            ? (int) round($talkedCalls->avg(function (Call $call) {
                return (int) ($call->dialog_duration ?? 0);
            }))
            : 0;

        return [
            'today_total_calls' => $totalCalls,
            'today_inbound_calls' => $inboundCalls,
            'today_outbound_calls' => $outboundCalls,
            'today_surveyed_calls' => $surveyedCalls,
            'today_pending_surveys' => $pendingSurveys,
            'today_talked_calls' => $talkedCalls->count(),
            'today_avg_talk_seconds' => $avgTalkSeconds,
            'today_avg_talk_human' => $this->formatDuration($avgTalkSeconds),
        ];
    }

    protected function formatCallRow(Call $call): array
    {
        return [
            'id' => (int) $call->id,
            'uuid' => (string) $call->uuid,
            'phone' => (string) $call->client_telephone,
            'operator_name' => (string) optional($call->operator)->name,
            'gateway' => (string) $call->gateway,
            'direction' => (string) $call->direction,
            'created_at' => (string) $call->created_at,
            'call_duration_seconds' => (int) ($call->call_duration ?? 0),
            'call_duration_human' => $this->formatDuration((int) ($call->call_duration ?? 0)),
            'dialog_duration_seconds' => (int) ($call->dialog_duration ?? 0),
            'dialog_duration_human' => $this->formatDuration((int) ($call->dialog_duration ?? 0)),
            'has_survey' => !empty($call->bitrixSurvey),
            'survey_reason' => (string) optional($call->bitrixSurvey)->reason_label,
            'survey_status' => (string) optional($call->bitrixSurvey)->status_label,
            'survey_comment' => (string) optional($call->bitrixSurvey)->comment_text,
            'survey_id' => optional($call->bitrixSurvey)->id ? (int) $call->bitrixSurvey->id : null,
            'store_url' => route('operator.call-surveys.store', ['call' => $call->id]),
            'report_url' => route('operator.report.calls', ['phone' => $call->client_telephone]),
        ];
    }

    protected function formatSurveyRow(Call $call): array
    {
        return [
            'call_id' => (int) $call->id,
            'phone' => (string) $call->client_telephone,
            'created_at' => (string) optional($call->bitrixSurvey)->created_at,
            'reason_label' => (string) optional($call->bitrixSurvey)->reason_label,
            'status_label' => (string) optional($call->bitrixSurvey)->status_label,
            'comment_text' => (string) optional($call->bitrixSurvey)->comment_text,
            'survey_id' => optional($call->bitrixSurvey)->id ? (int) $call->bitrixSurvey->id : null,
            'report_url' => route('operator.report.calls', ['phone' => $call->client_telephone]),
        ];
    }

    protected function formatPopupCall(?Call $call, $liveCall = null): array
    {
        $isActive = $liveCall && empty($liveCall->end_stamp);
        $livePhone = $liveCall ? (PhoneNumber::formatUz($liveCall->caller_id_number) ?? (string) $liveCall->caller_id_number) : '';
        $liveStart = $liveCall && !empty($liveCall->start_stamp)
            ? Carbon::createFromTimestamp((int) $liveCall->start_stamp)->toDateTimeString()
            : null;
        $popupWindowAllowed = $call ? $this->shouldAutoOpenSurveyPopup($call) : false;

        return [
            'key' => $call ? ('call:' . $call->id) : ('uuid:' . ($liveCall->uuid ?? '')),
            'mode' => $isActive ? 'active_call' : 'pending_survey',
            'title' => $isActive ? 'Входящий звонок' : 'Анкета по звонку',
            'message' => $isActive
                ? 'Разговор уже начался. Окно останется открытым и после завершения звонка.'
                : 'Анкета ещё не заполнена. Заполните её, чтобы опрос попал в общий отчёт.',
            'can_submit' => $call !== null,
            'is_active' => $isActive,
            'call_id' => $call ? (int) $call->id : null,
            'call_uuid' => $call ? (string) $call->uuid : (string) ($liveCall->uuid ?? ''),
            'phone' => $call ? (string) $call->client_telephone : $livePhone,
            'operator_name' => $call ? (string) optional($call->operator)->name : '',
            'gateway' => $call ? (string) $call->gateway : (string) ($liveCall->gateway ?? ''),
            'direction' => $call ? (string) $call->direction : 'inbound',
            'created_at' => $call ? (string) $call->created_at : $liveStart,
            'call_duration_human' => $call
                ? $this->formatDuration((int) ($call->call_duration ?? 0))
                : $this->formatDuration((int) ($liveCall->duration ?? 0)),
            'dialog_duration_human' => $call
                ? $this->formatDuration((int) ($call->dialog_duration ?? 0))
                : $this->formatDuration((int) ($liveCall->user_talk_time ?? 0)),
            'store_url' => $call ? route('operator.call-surveys.store', ['call' => $call->id]) : null,
            'popup_window_allowed' => $popupWindowAllowed,
            'popup_window_url' => $popupWindowAllowed
                ? route('operator.call-surveys.create', ['call' => $call->id, 'back' => route('operator.workspace'), 'popup' => 1])
                : null,
            'report_url' => route('operator.report.calls', ['phone' => $call ? $call->client_telephone : $livePhone]),
            'has_survey' => $call ? !empty($call->bitrixSurvey) : false,
        ];
    }

    protected function formatLiveOnlyPopup($liveCall, $operator): array
    {
        $isActive = empty($liveCall->end_stamp);
        $startAt = !empty($liveCall->start_stamp)
            ? Carbon::createFromTimestamp((int) $liveCall->start_stamp)->toDateTimeString()
            : null;
        $phone = PhoneNumber::formatUz($liveCall->caller_id_number ?? '') ?? (string) ($liveCall->caller_id_number ?? '');

        return [
            'key' => 'uuid:' . (string) ($liveCall->uuid ?? ''),
            'mode' => $isActive ? 'active_call' : 'awaiting_call_sync',
            'title' => $isActive ? 'Входящий звонок' : 'Звонок завершён',
            'message' => $isActive
                ? 'Окно открылось сразу по живому звонку. Анкету можно будет сохранить, когда запись синхронизируется.'
                : 'Разговор завершён. Ждём запись звонка, после этого анкета станет доступна для сохранения.',
            'can_submit' => false,
            'is_active' => $isActive,
            'call_id' => null,
            'call_uuid' => (string) ($liveCall->uuid ?? ''),
            'phone' => $phone,
            'operator_name' => (string) ($operator->name ?? ''),
            'gateway' => (string) ($liveCall->gateway ?? ''),
            'direction' => 'inbound',
            'created_at' => $startAt,
            'call_duration_human' => $this->formatDuration((int) ($liveCall->duration ?? 0)),
            'dialog_duration_human' => $this->formatDuration((int) ($liveCall->user_talk_time ?? 0)),
            'store_url' => null,
            'popup_window_allowed' => false,
            'popup_window_url' => null,
            'report_url' => route('operator.report.calls', ['phone' => $phone]),
            'has_survey' => false,
        ];
    }

    protected function shouldAutoOpenSurveyPopup(Call $call): bool
    {
        if ((int) ($call->dialog_duration ?? 0) <= 0 || !empty($call->bitrixSurvey)) {
            return false;
        }

        return Carbon::parse($call->created_at)
            ->greaterThanOrEqualTo(Carbon::now()->subMinutes(self::AUTO_POPUP_WINDOW_MINUTES));
    }

    protected function emptyStats(): array
    {
        return [
            'today_total_calls' => 0,
            'today_inbound_calls' => 0,
            'today_outbound_calls' => 0,
            'today_surveyed_calls' => 0,
            'today_pending_surveys' => 0,
            'today_talked_calls' => 0,
            'today_avg_talk_seconds' => 0,
            'today_avg_talk_human' => '0:00',
        ];
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
