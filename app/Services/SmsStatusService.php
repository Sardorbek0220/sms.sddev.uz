<?php

namespace App\Services;

use App\Call;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * SmsStatusService — readonly SMS-related counters.
 *
 * Eligibility rule (matches Console/Kernel.php scheduler):
 *   - event = call_end
 *   - dialog_duration >= 30
 *   - same client_telephone not already SMS-ed in last 4h
 */
class SmsStatusService
{
    const ELIGIBLE_DURATION_SEC = 30;

    /**
     * SMS counters for a date range.
     * Returns ['eligible','sent','pending','sent_today','pending_today'].
     */
    public function counters($from = null, $to = null, $gateway = null): array
    {
        $start = $from ? Carbon::parse($from)->startOfDay() : Carbon::today()->startOfDay();
        $end   = $to   ? Carbon::parse($to)->endOfDay()     : Carbon::today()->endOfDay();

        $base = Call::query()
            ->where('event', 'call_end')
            ->whereBetween('created_at', [$start, $end])
            ->where('dialog_duration', '>=', self::ELIGIBLE_DURATION_SEC);

        if ($gateway) $base->where('gateway', (int)$gateway);

        $total = (clone $base)->count();
        $sent = (clone $base)->where('sent_sms', 1)->count();

        return [
            'eligible' => (int)$total,
            'sent'     => (int)$sent,
            'pending'  => (int)$total - (int)$sent,
            'rate'     => $total > 0 ? round($sent / $total * 100, 1) : 0.0,
        ];
    }

    /**
     * Last N SMS-sent calls for activity log.
     * Returns array of stdClass: [id, client_telephone, gateway, dialog_duration, updated_at].
     */
    public function recentSent($limit = 10, $gateway = null): array
    {
        $q = DB::table('calls')
            ->select('id', 'client_telephone', 'gateway', 'dialog_duration', 'updated_at')
            ->where('event', 'call_end')
            ->where('sent_sms', 1)
            ->orderByDesc('updated_at')
            ->limit($limit);
        if ($gateway) $q->where('gateway', (int)$gateway);

        return $q->get()->all();
    }
}
