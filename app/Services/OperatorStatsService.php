<?php

namespace App\Services;

use App\Operator;
use App\Operator_time;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * OperatorStatsService — per-operator stats and live presence.
 */
class OperatorStatsService
{
    /** Operator considered "online" if last register ping is within this many seconds. */
    const ONLINE_WINDOW_SEC = 120;

    /**
     * Snapshot of currently online operators with status and last-ping age.
     *
     * Status determination:
     *  - online        — has open session (register=1, unregister=0) and recent timestamp_reg
     *  - on_call       — same + has active call in last 60s (event != call_end)
     *  - idle          — online but no recent call activity
     *  - offline       — no recent register or session closed
     *
     * Returns [['id','name','status','last_seen_ts','last_seen_human','color']].
     */
    public function liveOperators(): array
    {
        $now = time();
        $threshold = $now - self::ONLINE_WINDOW_SEC;

        // Latest open session per operator
        $sessions = Operator_time::query()
            ->where('register', 1)
            ->where(function ($q) {
                $q->where('unregister', 0)->orWhereNull('unregister');
            })
            ->where('timestamp_reg', '>=', $threshold - 3600) // last hour
            ->orderByDesc('timestamp_reg')
            ->get()
            ->keyBy('operator_id');

        // Operators with active inbound calls in last 60s
        $onCall = DB::table('calls')
            ->select('operator_id')
            ->where('event', '!=', 'call_end')
            ->where('created_at', '>=', date('Y-m-d H:i:s', $now - 60))
            ->groupBy('operator_id')
            ->pluck('operator_id')
            ->all();
        $onCallSet = array_flip($onCall);

        $operators = Operator::query()
            ->where('active', '!=', '0')
            ->orderBy('name')
            ->get();

        $out = [];
        foreach ($operators as $op) {
            $session = $sessions->get($op->id);
            $lastSeenTs = $session ? (int)$session->timestamp_reg : 0;

            if ($session && $lastSeenTs >= $threshold) {
                $status = isset($onCallSet[$op->id]) ? 'on_call' : 'idle';
            } elseif ($lastSeenTs > 0) {
                $status = 'offline';
            } else {
                $status = 'offline';
            }

            $out[] = [
                'id'              => (int)$op->id,
                'name'            => $op->name,
                'phone'           => $op->phone,
                'color'           => $op->color ?? '#64748b',
                'status'          => $status,
                'last_seen_ts'    => $lastSeenTs,
                'last_seen_human' => $lastSeenTs ? ph_relative_time($lastSeenTs) : '—',
            ];
        }
        return $out;
    }

    /**
     * Per-operator daily call stats.
     * Returns [['operator_id','total','answered','missed','avg_duration','sms_sent','feedback']].
     */
    public function dailyByOperator($date = null, $gateway = null): array
    {
        $date = $date ? Carbon::parse($date)->toDateString() : Carbon::today()->toDateString();

        $rows = DB::table('calls')
            ->selectRaw(
                'operator_id,
                 COUNT(*) AS total,
                 SUM(CASE WHEN dialog_duration >= 1 THEN 1 ELSE 0 END) AS answered,
                 SUM(CASE WHEN dialog_duration > 0 THEN dialog_duration ELSE 0 END) AS sum_dur,
                 SUM(CASE WHEN dialog_duration > 0 THEN 1 ELSE 0 END) AS dur_count,
                 SUM(CASE WHEN sent_sms = 1 THEN 1 ELSE 0 END) AS sms_sent'
            )
            ->where('event', 'call_end')
            ->whereDate('created_at', $date)
            ->when($gateway, function ($q) use ($gateway) { $q->where('gateway', (int)$gateway); })
            ->groupBy('operator_id')
            ->orderByDesc('total')
            ->get();

        // Feedback per operator (via calls join)
        $feedback = DB::table('feedback')
            ->join('calls', 'calls.id', '=', 'feedback.call_id')
            ->selectRaw('calls.operator_id, COUNT(*) AS c')
            ->whereDate('calls.created_at', $date)
            ->when($gateway, function ($q) use ($gateway) { $q->where('calls.gateway', (int)$gateway); })
            ->groupBy('calls.operator_id')
            ->pluck('c', 'calls.operator_id');

        $out = [];
        foreach ($rows as $r) {
            $opId = (int)$r->operator_id;
            $avg = (int)$r->dur_count > 0 ? (int)round((int)$r->sum_dur / (int)$r->dur_count) : 0;
            $out[] = [
                'operator_id'   => $opId,
                'total'         => (int)$r->total,
                'answered'      => (int)$r->answered,
                'missed'        => (int)$r->total - (int)$r->answered,
                'avg_duration'  => $avg,
                'sms_sent'      => (int)$r->sms_sent,
                'feedback'      => (int)($feedback[$opId] ?? 0),
            ];
        }
        return $out;
    }

    /**
     * Per-day call stats for a specific operator (for personal dashboard).
     * Returns last $days days, e.g. [{date,total,answered,avg_score}].
     */
    public function recentDays($operatorId, $days = 14): array
    {
        $from = Carbon::today()->subDays($days - 1)->startOfDay();
        $rows = DB::table('calls')
            ->selectRaw('DATE(created_at) AS d,
                COUNT(*) AS total,
                SUM(CASE WHEN dialog_duration >= 1 THEN 1 ELSE 0 END) AS answered')
            ->where('event', 'call_end')
            ->where('operator_id', (int)$operatorId)
            ->where('created_at', '>=', $from)
            ->groupBy('d')
            ->orderBy('d')
            ->get()
            ->keyBy('d');

        $out = [];
        for ($i = 0; $i < $days; $i++) {
            $d = Carbon::today()->subDays($days - 1 - $i)->toDateString();
            $r = $rows->get($d);
            $out[] = [
                'date'     => $d,
                'total'    => $r ? (int)$r->total : 0,
                'answered' => $r ? (int)$r->answered : 0,
            ];
        }
        return $out;
    }

    /**
     * Working time today for operator (sum of register..unregister gaps).
     * Returns seconds.
     */
    public function workingTimeToday($operatorId): int
    {
        $start = Carbon::today()->timestamp;
        $end = Carbon::today()->endOfDay()->timestamp;

        $sessions = DB::table('operator_times')
            ->where('operator_id', (int)$operatorId)
            ->where(function ($q) use ($start) {
                $q->where('timestamp_reg', '>=', $start)
                  ->orWhere('timestamp_unreg', '>=', $start);
            })
            ->orderBy('timestamp_reg')
            ->get();

        $total = 0;
        foreach ($sessions as $s) {
            $a = max((int)$s->timestamp_reg, $start);
            $b = (int)$s->timestamp_unreg ?: time();
            $b = min($b, $end);
            if ($b > $a) $total += $b - $a;
        }
        return (int)$total;
    }
}
