<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\CallReportService;
use App\Services\FeedbackReportService;
use App\Services\OperatorStatsService;
use App\Services\SmsStatusService;
use App\Services\GatewayService;
use Illuminate\Http\Request;

/**
 * DashboardController — Phase 4 admin overview page.
 *
 * Read-only aggregator: pulls daily stats from services and renders
 * `admin.dashboard` view. Doesn't touch business logic, cron, or webhooks.
 *
 * Route:    GET /admin/dashboard  (admin middleware)
 * View:     resources/views/admin/dashboard.blade.php
 */
class DashboardController extends Controller
{
    public function index(
        Request $request,
        CallReportService $calls,
        FeedbackReportService $feedback,
        SmsStatusService $sms,
        OperatorStatsService $operators
    ) {
        $date = $request->get('date');           // YYYY-MM-DD
        $gateway = $request->get('gateway');     // int or null

        $daily = $calls->dailyStats($date, $gateway);
        $conv = $calls->conversion($date, $gateway);
        $byGw = $calls->byGateway($date);
        $hourly = $calls->hourly($date, $gateway);

        $fbSummary = $feedback->summary($date, $date, $gateway);

        $smsCounters = $sms->counters($date, $date, $gateway);

        $live = $operators->liveOperators();
        $online = 0;
        $onCall = 0;
        $idle = 0;
        $offline = 0;
        foreach ($live as $op) {
            if ($op['status'] === 'online' || $op['status'] === 'idle') $idle++;
            elseif ($op['status'] === 'on_call') $onCall++;
            elseif ($op['status'] === 'offline') $offline++;
            if (in_array($op['status'], ['online', 'idle', 'on_call'], true)) $online++;
        }

        return view('admin.dashboard', [
            'date'           => $daily['date'],
            'gateway'        => $gateway,
            'gatewayOptions' => GatewayService::options(),
            'daily'          => $daily,
            'conv'           => $conv,
            'byGateway'      => $byGw,
            'hourly'         => $hourly,
            'fbSummary'      => $fbSummary,
            'smsCounters'    => $smsCounters,
            'liveOperators'  => $live,
            'live'           => [
                'online'  => $online,
                'on_call' => $onCall,
                'idle'    => $idle,
                'offline' => $offline,
            ],
        ]);
    }
}
