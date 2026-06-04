<?php

namespace App\Services;

use App\Call;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * CallReportService — read-only aggregations on `calls` table.
 *
 * Used by the new dashboard / reports (Phase 4-5).
 * All methods return plain arrays so they're trivially passable to Blade.
 */
class CallReportService
{
    /** Considered "answered" if dialog_duration > 0. */
    const MIN_ANSWERED_SECONDS = 1;

    /**
     * High-level counters for a single day (defaults to today).
     * Returns [
     *   'date', 'total', 'inbound', 'outbound', 'answered',
     *   'missed', 'avg_duration', 'sms_sent', 'sms_pending',
     *   'feedback_received',
     * ]
     */
    public function dailyStats($date = null, $gateway = null): array
    {
        $date = $date ? Carbon::parse($date)->toDateString() : Carbon::today()->toDateString();

        // Counters include both call_end (completed) and call_missed (rang, no answer).
        $base = Call::query()
            ->whereIn('event', ['call_end', 'call_missed'])
            ->whereDate('created_at', $date);
        if ($gateway) $base->where('gateway', (int)$gateway);

        // SMS-related counts only apply to completed (call_end) calls.
        $smsBase = Call::query()
            ->where('event', 'call_end')
            ->whereDate('created_at', $date);
        if ($gateway) $smsBase->where('gateway', (int)$gateway);

        $total    = (clone $base)->count();
        $inbound  = (clone $base)->where('direction', 'inbound')->count();
        $outbound = (clone $base)->where('direction', 'outbound')->count();
        // Answered = actually had a conversation. call_missed never qualifies.
        $answered = (clone $smsBase)->where('dialog_duration', '>=', self::MIN_ANSWERED_SECONDS)->count();
        // Missed = call_missed events PLUS call_end with zero dialog (technically completed but no talk).
        $missed = $total - $answered;

        $avg = (clone $smsBase)
            ->where('dialog_duration', '>', 0)
            ->avg('dialog_duration');

        $smsSent = (clone $smsBase)->where('sent_sms', 1)->count();
        $smsPending = (clone $smsBase)
            ->where('dialog_duration', '>=', 30)
            ->where('sent_sms', 0)
            ->count();

        $feedbackReceived = DB::table('feedback')
            ->join('calls', 'calls.id', '=', 'feedback.call_id')
            ->whereDate('calls.created_at', $date)
            ->when($gateway, function ($q) use ($gateway) { $q->where('calls.gateway', (int)$gateway); })
            ->count();

        return [
            'date'              => $date,
            'total'             => (int)$total,
            'inbound'           => (int)$inbound,
            'outbound'          => (int)$outbound,
            'answered'          => (int)$answered,
            'missed'            => (int)$missed,
            'avg_duration'      => (int)round((float)$avg),
            'sms_sent'          => (int)$smsSent,
            'sms_pending'       => (int)$smsPending,
            'feedback_received' => (int)$feedbackReceived,
        ];
    }

    /**
     * Conversion: call → SMS → feedback for the given day.
     * Returns ['eligible_for_sms','sms_sent','feedback_received','sms_rate','feedback_rate'].
     * Eligible-for-SMS = answered calls >= 30s.
     */
    public function conversion($date = null, $gateway = null): array
    {
        $date = $date ? Carbon::parse($date)->toDateString() : Carbon::today()->toDateString();

        $base = Call::query()
            ->where('event', 'call_end')
            ->whereDate('created_at', $date);
        if ($gateway) $base->where('gateway', (int)$gateway);

        $eligible = (clone $base)->where('dialog_duration', '>=', 30)->count();
        $smsSent = (clone $base)->where('sent_sms', 1)->count();
        $feedback = DB::table('feedback')
            ->join('calls', 'calls.id', '=', 'feedback.call_id')
            ->whereDate('calls.created_at', $date)
            ->when($gateway, function ($q) use ($gateway) { $q->where('calls.gateway', (int)$gateway); })
            ->count();

        return [
            'eligible_for_sms'   => (int)$eligible,
            'sms_sent'           => (int)$smsSent,
            'feedback_received'  => (int)$feedback,
            'sms_rate'           => $eligible > 0 ? round($smsSent / $eligible * 100, 1) : 0.0,
            'feedback_rate'      => $smsSent > 0 ? round($feedback / $smsSent * 100, 1) : 0.0,
        ];
    }

    /**
     * Hourly distribution of calls for a day.
     * Returns array[24] of ['hour'=>0..23,'total','answered','missed'].
     */
    public function hourly($date = null, $gateway = null): array
    {
        $date = $date ? Carbon::parse($date)->toDateString() : Carbon::today()->toDateString();

        $rows = Call::query()
            ->selectRaw('HOUR(created_at) AS h, COUNT(*) AS total, SUM(CASE WHEN event = "call_end" AND dialog_duration >= ? THEN 1 ELSE 0 END) AS answered',
                [self::MIN_ANSWERED_SECONDS])
            ->whereIn('event', ['call_end', 'call_missed'])
            ->whereDate('created_at', $date)
            ->when($gateway, function ($q) use ($gateway) { $q->where('gateway', (int)$gateway); })
            ->groupBy('h')
            ->get()
            ->keyBy('h');

        $out = [];
        for ($h = 0; $h < 24; $h++) {
            $r = $rows->get($h);
            $total = $r ? (int)$r->total : 0;
            $answered = $r ? (int)$r->answered : 0;
            $out[] = [
                'hour'     => $h,
                'total'    => $total,
                'answered' => $answered,
                'missed'   => $total - $answered,
            ];
        }
        return $out;
    }

    /**
     * Counts grouped by gateway for a date (for company breakdown card).
     * Returns [['gateway','name','total','answered','missed']].
     */
    public function byGateway($date = null): array
    {
        $date = $date ? Carbon::parse($date)->toDateString() : Carbon::today()->toDateString();

        $rows = Call::query()
            ->selectRaw('gateway, COUNT(*) AS total, SUM(CASE WHEN event = "call_end" AND dialog_duration >= ? THEN 1 ELSE 0 END) AS answered',
                [self::MIN_ANSWERED_SECONDS])
            ->whereIn('event', ['call_end', 'call_missed'])
            ->whereDate('created_at', $date)
            ->groupBy('gateway')
            ->orderByDesc('total')
            ->get();

        $out = [];
        foreach ($rows as $r) {
            $out[] = [
                'gateway'  => (int)$r->gateway,
                'name'     => GatewayService::name($r->gateway),
                'total'    => (int)$r->total,
                'answered' => (int)$r->answered,
                'missed'   => (int)$r->total - (int)$r->answered,
            ];
        }
        return $out;
    }

    /**
     * Convenience: derive call status string.
     * Returns one of: 'answered'|'missed'.
     */
    public static function statusOf($call): string
    {
        $d = is_object($call) ? (int)($call->dialog_duration ?? 0) : (int)$call;
        return $d >= self::MIN_ANSWERED_SECONDS ? 'answered' : 'missed';
    }
}
