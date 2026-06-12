<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Response;
use App\All_call;
use App\Operator;
use App\Feedback;
use App\Operator_time;
use App\Unknown_client;
use App\Holiday;
use App\Score;
use App\Exception;
use App\Call;
use App\Services\BitrixSurveyService;
use App\Services\CallAnalyticsService;
use App\Services\OperatorWorkspaceService;
use App\Services\PbxLiveStateService;
use App\Support\PhoneNumber;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    const url = "https://api.workly.uz/v1/oauth/token";
    const client_id = "d63626d920a18be0ce20fa5ea6768b2c662a17dfd3b93";
    const client_secret = "8988c625e95402bb7eba5cc622247d68662a17dfd3b99";
    const username = "oybek.mirkasimov@gmail.com";
    const password = "P@ssw0rd";
    const workly_auth = 'configs/workly_auth.json';
    private const ONLINEPBX_DOMAIN = 'pbx12127.onpbx.ru';
    private const ONLINEPBX_AUTH_KEY = 'OGV3MWNuVkw0VWJuZHc3c1lUeFViaWVJYnA5UXdGaXM';

    public function timeExceptions($from_unix, $to_unix)
    {
        $from = gmdate("Y-m-d H:i:s", $from_unix);
        $to = gmdate("Y-m-d H:i:s", $to_unix);        

        $exceptions = Exception::whereBetween('day', [$from, $to])->get();
        $excCondition = "";
        foreach ($exceptions as $exc) {
            $exc_from = strtotime(substr($exc->day, 0, -9) . " " . $exc->from_exc . ":00");
            $exc_to = strtotime(substr($exc->day, 0, -9) . " " . $exc->to_exc . ":00");
            $excCondition .= " AND start_stamp NOT BETWEEN $exc_from AND $exc_to";
        }  
        return $excCondition;      
    }

    public function timeExceptionsTalk($from_unix, $to_unix)
    {
        $from = gmdate("Y-m-d H:i:s", $from_unix);
        $to = gmdate("Y-m-d H:i:s", $to_unix);        

        $exceptions = Exception::whereBetween('day', [$from, $to])->get();
        if (!empty($exceptions)) {
            $excConditions = [];
            foreach ($exceptions as $exc) {
                $exc_from = strtotime(substr($exc->day, 0, -9) . " " . $exc->from_exc . ":00");
                $exc_to = strtotime(substr($exc->day, 0, -9) . " " . $exc->to_exc . ":00");
                $excConditions[] = "(start_stamp BETWEEN $exc_from AND $exc_to)";
            }  
            return "user_talk_time > 0 AND (" . implode(" OR ", $excConditions) . ")";
        }else{
            return "";
        }
    }

    public function index(Request $request)
    {
        if ($request->from_date == null) {
            $from_date = date('Y-m-d');
        }else{
            $from_date = $request->from_date;
        }

        if ($request->to_date == null) {
            $to_date = date('Y-m-d');
        }else{
            $to_date = $request->to_date;
        } 

        // ----- first report -----

        $reports = DB::table('feedback')
            ->leftJoin('calls', 'feedback.call_id', '=', 'calls.id')
            ->leftJoin('operators', 'calls.operator_id', '=', 'operators.id')
            ->select(
                DB::raw('SUM(CASE WHEN feedback.solved = 0 THEN 1 ELSE 0 END) AS mark0'),
                DB::raw('SUM(CASE WHEN feedback.solved = 1 THEN 1 ELSE 0 END) AS mark1'),
                DB::raw('SUM(CASE WHEN feedback.solved = 2 THEN 1 ELSE 0 END) AS mark2'),
                DB::raw('SUM(CASE WHEN feedback.solved = 3 THEN 1 ELSE 0 END) AS mark3'),
                'operators.name'
            )
            ->whereBetween('feedback.created_at', [$from_date." 00:00:00", $to_date." 23:59:59"])
            ->groupBy('calls.operator_id')
            ->get();  

        $total = [
            'mark0' => 0,
            'mark1' => 0,
            'mark2' => 0,
            'mark3' => 0,
            'total' => 0,
            'name' => 'Total'
        ];
        foreach ($reports as $report) {
            $total['mark0'] += $report->mark0;
            $total['mark1'] += $report->mark1;
            $total['mark2'] += $report->mark2;
            $total['mark3'] += $report->mark3;
            $report->total = $report->mark0 + $report->mark1 + $report->mark2 + $report->mark3;
            $total['total'] += $report->total;
            $report->percent = $report->total == 0 ? 0 : number_format(($report->mark3/$report->total)*100, 1) . " %";
        }

        $footReports[] = (object) $total;
        $footReports[] = (object) 
        [
            'mark0' => $total['total'] == 0 ? 0 : number_format(($total['mark0']/$total['total'])*100, 1) . " %",
            'mark1' => $total['total'] == 0 ? 0 : number_format(($total['mark1']/$total['total'])*100, 1) . " %",
            'mark2' => $total['total'] == 0 ? 0 : number_format(($total['mark2']/$total['total'])*100, 1) . " %",
            'mark3' => $total['total'] == 0 ? 0 : number_format(($total['mark3']/$total['total'])*100, 1) . " %",
            'total' => 100 . " %",
            'name' => ''
        ];

        // ----- second report -----

        $reports_by_date = DB::table('feedback')
            ->select(
                DB::raw('SUM(CASE WHEN solved = 0 THEN 1 ELSE 0 END) AS mark0'),
                DB::raw('SUM(CASE WHEN solved = 1 THEN 1 ELSE 0 END) AS mark1'),
                DB::raw('SUM(CASE WHEN solved = 2 THEN 1 ELSE 0 END) AS mark2'),
                DB::raw('SUM(CASE WHEN solved = 3 THEN 1 ELSE 0 END) AS mark3'),
                DB::raw('DATE(created_at) day'),
            )
            ->whereBetween('created_at', [$from_date." 00:00:00", $to_date." 23:59:59"])
            ->groupBy('day')
            ->get();  

        $footReportsByDate = [];
        $footReportsByPercent = [];
        $Total = [
            'mark0' => 0,
            'mark1' => 0,
            'mark2' => 0,
            'mark3' => 0,
            'total' => 0,
            'day' => 'Total'
        ];
        foreach ($reports_by_date as $report) {
            $all_marks = ($report->mark0 + $report->mark1 + $report->mark2 + $report->mark3);
            $report->day = date("Y/m/d", strtotime($report->day));
            $footReportsByDate[] = (object) ['total' => $all_marks];
            $Total['total'] += $all_marks;
            $Total['mark0'] += $report->mark0;
            $Total['mark1'] += $report->mark1;
            $Total['mark2'] += $report->mark2;
            $Total['mark3'] += $report->mark3;
            $footReportsByPercent[] = (object) ['percent' => $all_marks == 0 ? 0 : number_format(($report->mark3/$all_marks)*100, 1) . " %"];
        }

        $reports_by_date[] = (object) $Total;
        $footReportsByDate[] = (object) ['total' => $Total['total']];
        $footReportsByPercent[] = (object) ['percent' => $Total['total'] == 0 ? 0 : number_format(($Total['mark3']/$Total['total'])*100, 1) . " %"];

        $reports_by_date[] = (object) [
            'mark0' => $Total['total'] == 0 ? 0 : number_format(($Total['mark0']/$Total['total'])*100, 1) . " %",
            'mark1' => $Total['total'] == 0 ? 0 : number_format(($Total['mark1']/$Total['total'])*100, 1) . " %",
            'mark2' => $Total['total'] == 0 ? 0 : number_format(($Total['mark2']/$Total['total'])*100, 1) . " %",
            'mark3' => $Total['total'] == 0 ? 0 : number_format(($Total['mark3']/$Total['total'])*100, 1) . " %",
            'total' => "100 %",
            'day' => '(%)'
        ];
        $footReportsByDate[] = (object) ['total' => '100 %'];
        $footReportsByPercent[] = (object) ['percent' => ''];
        
        return view('admin.report.index', compact('reports', 'reports_by_date', 'footReports', 'footReportsByDate', 'footReportsByPercent', 'Total', 'from_date', 'to_date'));
    }

    public function calls(Request $request, BitrixSurveyService $bitrixSurveyService)
    {
        if ($request->from_date == null) {
            $from_date = date('Y-m-d');
        }else{
            $from_date = $request->from_date;
        }

        if ($request->to_date == null) {
            $to_date = date('Y-m-d');
        }else{
            $to_date = $request->to_date;
        }

        $phone = trim((string) $request->phone);
        $phoneFilter = PhoneNumber::canonicalUzDigits($phone);

        // Phase 5: optional filters
        $gateway      = $request->input('gateway');                 // int
        $direction    = $request->input('direction');               // 'inbound' | 'outbound'
        $statusFilter = $request->input('status_call');             // 'answered' | 'missed'
        $hasSms       = $request->input('has_sms');                 // 'yes' | 'no'
        $hasFeedback  = $request->input('has_feedback');            // 'yes' | 'no'
        $sort         = $request->input('sort', '');                // 'b24_desc' | 'b24_asc' | ''

        // b24-sort: precompute surveyed call IDs once (used by both the Анкета filter
        // and the "Действия / Bitrix" column sort).
        $surveyedCallIds = [];
        if (in_array($hasFeedback, ['yes', 'no'], true) || in_array($sort, ['b24_desc', 'b24_asc'], true)) {
            $matchH = (int) env('BITRIX_SURVEY_MATCH_HOURS', 72);
            $svFrom = Carbon::parse($from_date . ' 00:00:00')->subHours($matchH);
            $svTo   = Carbon::parse($to_date . ' 23:59:59')->addDays(30);

            $rawIds = DB::connection('bitrix_survey')
                ->table(env('BITRIX_SURVEY_DB_TABLE', 'b24_call_survey_logs'))
                ->whereBetween('created_at', [$svFrom, $svTo])
                ->where('call_id', 'like', '%:call:%')
                ->pluck('call_id');
            $ids = [];
            foreach ($rawIds as $cid) {
                if (preg_match('|:call:(\d+):|', (string) $cid, $m)) {
                    $ids[(int) $m[1]] = true;
                }
            }
            $surveyedCallIds = array_keys($ids);
        }

        $data = Call::with('operator')
            ->whereBetween('created_at', [$from_date." 00:00:00", $to_date." 23:59:59"])
            ->when(Auth::check() && Auth::user()->isOperator(), function ($query) {
                return $query->where('operator_id', Auth::user()->operator_id);
            })
            ->when($phone !== '', function ($query) use ($phoneFilter) {
                if ($phoneFilter === null) {
                    return $query;
                }

                return $query->whereRaw(
                    PhoneNumber::mysqlCanonicalUzDigitsExpression('client_telephone') . ' = ?',
                    [$phoneFilter]
                );
            })
            ->when($gateway, function ($q) use ($gateway) { return $q->where('gateway', (int)$gateway); })
            ->when(in_array($direction, ['inbound', 'outbound'], true), function ($q) use ($direction) {
                return $q->where('direction', $direction);
            })
            ->when($statusFilter === 'answered', function ($q) { return $q->where('dialog_duration', '>=', 1); })
            ->when($statusFilter === 'missed', function ($q) {
                return $q->where(function ($qq) { $qq->whereNull('dialog_duration')->orWhere('dialog_duration', '<', 1); });
            })
            ->when($hasSms === 'yes', function ($q) { return $q->where('sent_sms', 1); })
            ->when($hasSms === 'no', function ($q) { return $q->where('sent_sms', 0); })
            // anketa filter — uses precomputed $surveyedCallIds (above)
            ->when($hasFeedback === 'yes', function ($q) use ($surveyedCallIds) {
                $q->whereIn('calls.id', $surveyedCallIds ?: [0]);
            })
            ->when($hasFeedback === 'no', function ($q) use ($surveyedCallIds) {
                if (!empty($surveyedCallIds)) $q->whereNotIn('calls.id', $surveyedCallIds);
            });

        // sort: clickable column header on "Действия / Bitrix"
        if ($sort === 'b24_desc' && !empty($surveyedCallIds)) {
            // with-anketa first
            $list = implode(',', array_map('intval', $surveyedCallIds));
            $data->orderByRaw("FIND_IN_SET(calls.id, '{$list}') > 0 DESC");
        } elseif ($sort === 'b24_asc' && !empty($surveyedCallIds)) {
            // without-anketa first
            $list = implode(',', array_map('intval', $surveyedCallIds));
            $data->orderByRaw("FIND_IN_SET(calls.id, '{$list}') > 0 ASC");
        }
        $data->orderByDesc('created_at');

        // Phase 11: CSV export
        if ($request->input('export') === 'csv') {
            $export = (clone $data)->limit(5000)->get();
            $callIds = $export->pluck('id')->all();
            $feedbacks = !empty($callIds)
                ? \DB::table('feedback')->whereIn('call_id', $callIds)
                    ->select('call_id', 'q1', 'q2', 'q3', 'q4', 'solved')
                    ->get()->keyBy('call_id')
                : collect();

            $filename = 'calls_' . $from_date . '_' . $to_date . '.csv';
            return response()->stream(function () use ($export, $feedbacks) {
                $h = fopen('php://output', 'w');
                fwrite($h, "\xEF\xBB\xBF"); // UTF-8 BOM
                fputcsv($h, ['ID','Дата','Компания','Клиент','Оператор','Тип','Длительность (сек)','Статус','SMS','Анкета','Score'], ';');
                foreach ($export as $c) {
                    $fb = $feedbacks->get($c->id);
                    $score = $fb ? ((int)$fb->q1===1)+((int)$fb->q2===1)+((int)$fb->q3===1)+((int)$fb->q4===1) : '';
                    $isAns = (int)$c->dialog_duration >= 1;
                    fputcsv($h, [
                        $c->id,
                        (string)$c->created_at,
                        \App\Services\GatewayService::name($c->gateway),
                        (string)$c->client_telephone,
                        optional($c->operator)->name ?: '',
                        $c->direction === 'inbound' ? 'Входящий' : ($c->direction === 'outbound' ? 'Исходящий' : ''),
                        (int)$c->dialog_duration,
                        $isAns ? 'Отвечен' : 'Пропущен',
                        ((int)$c->sent_sms === 1) ? 'Да' : 'Нет',
                        $fb ? 'Да' : 'Нет',
                        $score === '' ? '' : $score . '/4',
                    ], ';');
                }
                fclose($h);
            }, 200, [
                'Content-Type' => 'text/csv; charset=utf-8',
                'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            ]);
        }

        $data = $data->paginate(100)->appends($request->query());

        $data->setCollection(
            $bitrixSurveyService->attachToCalls(collect($data->items()), $from_date, $to_date)
        );

        // Phase 5: eager-load feedback (q1..q4) for page items
        $callIds = collect($data->items())->pluck('id')->all();
        $feedbacks = !empty($callIds)
            ? \DB::table('feedback')->whereIn('call_id', $callIds)
                ->select('call_id', 'q1', 'q2', 'q3', 'q4', 'solved', 'complaint')
                ->get()->keyBy('call_id')
            : collect();
        foreach ($data->items() as $call) {
            $call->ph_feedback = $feedbacks->get($call->id);
        }

        if ($phoneFilter !== null) {
            $phone = PhoneNumber::formatUz($phoneFilter) ?? $phone;
        }

        return view('admin.report.calls', compact(
            'data', 'from_date', 'to_date', 'phone',
            'gateway', 'direction', 'statusFilter', 'hasSms', 'hasFeedback'
        ));
    }

    public function callbackAnalytics(Request $request, CallAnalyticsService $callAnalyticsService)
    {
        [$from_date, $to_date, $activePreset] = $this->resolveCallbackAnalyticsRange($request);

        $gateway = $request->input('gateway') !== null && $request->input('gateway') !== ''
            ? (int) $request->input('gateway') : null;

        $analytics = $callAnalyticsService->buildDashboard($from_date, $to_date, $gateway);

        return view('admin.report.callback-analytics', compact('analytics', 'from_date', 'to_date', 'activePreset', 'gateway'));
    }

    private function resolveCallbackAnalyticsRange(Request $request): array
    {
        $preset = trim((string) $request->query('preset', ''));
        $availablePresets = ['today', 'yesterday', 'week', 'month', 'previous_month'];
        $from_date = trim((string) $request->query('from_date', ''));
        $to_date = trim((string) $request->query('to_date', ''));

        $today = new \DateTimeImmutable('today');

        if (!in_array($preset, $availablePresets, true)) {
            $preset = '';
        }

        if ($preset !== '') {
            [$from_date, $to_date] = $this->callbackAnalyticsPresetRange($preset, $today);

            return [$from_date, $to_date, $preset];
        }

        if ($from_date !== '' || $to_date !== '') {
            if ($from_date === '') {
                $from_date = $to_date;
            }

            if ($to_date === '') {
                $to_date = $from_date;
            }

            return [$from_date, $to_date, $this->detectCallbackAnalyticsPreset($from_date, $to_date, $today)];
        }

        return [
            $today->format('Y-m-01'),
            $today->format('Y-m-d'),
            'month',
        ];
    }

    private function callbackAnalyticsPresetRange(string $preset, \DateTimeImmutable $today): array
    {
        if ($preset === 'today') {
            $value = $today->format('Y-m-d');

            return [$value, $value];
        }

        if ($preset === 'yesterday') {
            $value = $today->modify('-1 day')->format('Y-m-d');

            return [$value, $value];
        }

        if ($preset === 'week') {
            $weekStart = $today->modify('-' . (((int) $today->format('N')) - 1) . ' days');

            return [$weekStart->format('Y-m-d'), $today->format('Y-m-d')];
        }

        if ($preset === 'previous_month') {
            $previousMonth = $today->modify('first day of last month');

            return [
                $previousMonth->format('Y-m-01'),
                $previousMonth->format('Y-m-t'),
            ];
        }

        return [
            $today->format('Y-m-01'),
            $today->format('Y-m-d'),
        ];
    }

    private function detectCallbackAnalyticsPreset(string $from_date, string $to_date, \DateTimeImmutable $today): string
    {
        foreach (['today', 'yesterday', 'week', 'month', 'previous_month'] as $preset) {
            [$presetFrom, $presetTo] = $this->callbackAnalyticsPresetRange($preset, $today);

            if ($presetFrom === $from_date && $presetTo === $to_date) {
                return $preset;
            }
        }

        return '';
    }

    public function operatorWorkspace(OperatorWorkspaceService $operatorWorkspaceService)
    {
        abort_unless(Auth::check() && Auth::user()->isOperator(), 404);

        $workspaceData = $operatorWorkspaceService->buildForUser(Auth::user());

        // Phase 7: extra fields for the new layout. Kept separate from $workspaceData
        // so that /workspace/data JSON contract stays unchanged for JS pollers.
        $extra = ['avg_score' => 0.0, 'missed_today' => 0, 'working_seconds' => 0,
                  'recent_days' => [], 'fb_count_today' => 0];
        $opId = optional(Auth::user()->operator)->id;
        if ($opId) {
            $today = date('Y-m-d');
            $stats = app(\App\Services\OperatorStatsService::class);

            $extra['working_seconds'] = $stats->workingTimeToday($opId);
            $extra['recent_days']     = $stats->recentDays($opId, 14);

            $row = \DB::table('feedback')
                ->join('calls', 'calls.id', '=', 'feedback.call_id')
                ->where('calls.operator_id', $opId)
                ->whereDate('feedback.created_at', $today)
                ->selectRaw('COUNT(*) AS c,
                    SUM((CASE WHEN q1=1 THEN 1 ELSE 0 END)+(CASE WHEN q2=1 THEN 1 ELSE 0 END)
                        +(CASE WHEN q3=1 THEN 1 ELSE 0 END)+(CASE WHEN q4=1 THEN 1 ELSE 0 END)) AS sumYes')
                ->first();
            $extra['fb_count_today'] = $row ? (int)$row->c : 0;
            $extra['avg_score'] = ($row && (int)$row->c > 0) ? round((float)$row->sumYes / (float)$row->c, 2) : 0.0;

            $extra['missed_today'] = (int) Call::query()
                ->where('operator_id', $opId)
                ->where('event', 'call_end')
                ->whereDate('created_at', $today)
                ->where(function ($q) { $q->whereNull('dialog_duration')->orWhere('dialog_duration', '<', 1); })
                ->count();
        }

        return view('operator.workspace', compact('workspaceData', 'extra'));
    }

    public function operatorWorkspaceData(OperatorWorkspaceService $operatorWorkspaceService)
    {
        abort_unless(Auth::check() && Auth::user()->isOperator(), 404);

        return Response::json($operatorWorkspaceService->buildForUser(Auth::user()));
    }

    public function monitoringKeys()
    {
        $auth = $this->getMonitoringAuthData();
        $key_and_id = $auth->key_id.":".$auth->key;

        $auth_key = self::ONLINEPBX_AUTH_KEY;

        return Response::json(['key_and_id' => $key_and_id, 'auth_key' => $auth_key]);
    }

    public function monitoring()
    {
        return view('admin.monitoring');
    }

    public function monitoringLiveState(Request $request, PbxLiveStateService $pbxLiveStateService)
    {
        // Long-polling: hold the request until the snapshot version advances
        // past `since`, or until `wait` seconds elapse.
        $since   = (int) $request->query('since', 0);
        $maxWait = (int) $request->query('wait', 25);
        if ($maxWait < 1)  $maxWait = 1;
        if ($maxWait > 15) $maxWait = 15; // stay under php max_execution_time

        // Release session lock so other tabs aren't blocked while we wait.
        if (function_exists('session_write_close') && session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }
        @set_time_limit($maxWait + 5);

        $deadline = microtime(true) + $maxWait;
        $sleepUs  = 200000; // 200ms

        while (true) {
            $snap = $pbxLiveStateService->snapshot();
            $version = (int) ($snap['version'] ?? 0);

            if ($since === 0 || $version > $since) {
                return Response::json($snap);
            }
            if (microtime(true) >= $deadline) {
                return Response::json($snap); // timeout: return current state
            }
            // Bail early if the client disconnected.
            if (connection_aborted()) {
                return Response::json($snap);
            }
            usleep($sleepUs);
        }
    }

    public function monitoringFifo()
    {
        try {
            $payload = $this->fetchMonitoringFifo();
        } catch (\Throwable $exception) {
            return Response::json([
                'status' => '0',
                'comment' => 'fifo_unavailable',
                'message' => $exception->getMessage(),
                'data' => [],
            ], 200);
        }

        return Response::json([
            'status' => '1',
            'data' => $payload,
        ]);
    }

    /**
     * Build the inner SELECT that exposes `calls` rows (the same table the
     * dashboard reads) in the column shape the monitoring frontend expects
     * from `all_calls`. Wrapped in a subquery so the aliased `start_stamp` /
     * `user_talk_time` columns are usable in the outer WHERE (and by the
     * existing time-exception condition strings).
     */
    private function monitoringCallsSubquery($gateway): string
    {
        $gateway = (int) $gateway;

        return "SELECT
                c.gateway,
                c.direction AS accountcode,
                c.direction,
                UNIX_TIMESTAMP(c.created_at) AS start_stamp,
                (UNIX_TIMESTAMP(c.created_at) + c.call_duration) AS end_stamp,
                c.dialog_duration AS user_talk_time,
                c.call_duration AS duration,
                CASE WHEN c.direction = 'outbound' THEN o.phone ELSE c.client_telephone END AS caller_id_number,
                CASE WHEN c.direction = 'outbound' THEN c.client_telephone ELSE o.phone END AS destination_number,
                c.uuid
            FROM calls c
            LEFT JOIN operators o ON o.id = c.operator_id
            WHERE c.gateway = $gateway
              AND c.event IN ('call_end', 'call_missed')";
    }

    public function monitoringData(Request $request)
    {
        $from = (int) $request['from'];
        $to = (int) $request['to'];
        $gateway = $request['gateway'] ?? '712075995';

        $excCondition = $this->timeExceptions($from, $to);
        $inner = $this->monitoringCallsSubquery($gateway);

        $calls = DB::select("SELECT * FROM ($inner) t WHERE t.start_stamp BETWEEN $from AND $to $excCondition");

        if (!empty($excCondition)) {
            $excConditionTalk = $this->timeExceptionsTalk($from, $to);
            $callsTalk = DB::select("SELECT * FROM ($inner) t WHERE $excConditionTalk");
            if (!empty($callsTalk)) {
                $calls = array_merge($calls, $callsTalk);
            }
        }

        return Response::json($this->normalizeAllCallRows($calls));
    }

    public function monitoringUsers(Request $request)
    {
        $users = DB::table('operators')->select('name', 'phone as num', 'field', 'color', DB::raw('LENGTH(phone) as phone_len'))->where('active', 'Y')->having('phone_len', '>', 2)->get();

        return Response::json($users);
    }

    public function monitoringSurveysCount(Request $request)
    {
        // A call is "анкетировано" if a Bitrix24 survey exists with the same
        // normalized phone within ±72h (same logic BitrixSurveyService uses).
        // We do two indexed SELECTs and match in PHP — much faster than a
        // cross-database JOIN with on-the-fly phone normalization.

        $from = ($request->input('from') ?: date('Y-m-d')) . ' 00:00:00';
        $to   = ($request->input('to')   ?: date('Y-m-d')) . ' 23:59:59';
        $matchHours = (int) env('BITRIX_SURVEY_MATCH_HOURS', 72);
        $b24Table = env('BITRIX_SURVEY_DB_TABLE', 'b24_call_survey_logs');
        if (!preg_match('/^[A-Za-z0-9_]+$/', $b24Table)) $b24Table = 'b24_call_survey_logs';

        // 1. Total calls in period.
        $totalCalls = (int) DB::table('calls')
            ->whereBetween('created_at', [$from, $to])
            ->count();

        if ($totalCalls === 0) {
            return Response::json([
                'count' => 0, 'with_survey' => 0,
                'total_calls' => 0, 'percent' => 0.0,
            ]);
        }

        // 3. Pull all calls in period (id, normalized phone, ts).
        $calls = DB::table('calls')
            ->whereBetween('created_at', [$from, $to])
            ->select('id', 'client_telephone', 'created_at')
            ->get();

        $phoneIndex = []; // normalized_phone => [['id'=>int,'ts'=>int], ...]
        foreach ($calls as $c) {
            $norm = \App\Support\PhoneNumber::canonicalUzDigits($c->client_telephone);
            if ($norm === null) continue;
            $phoneIndex[$norm][] = [
                'id' => (int) $c->id,
                'ts' => strtotime($c->created_at),
            ];
        }

        // 4. Pull Bitrix surveys in extended window.
        $extFrom = \Carbon\Carbon::parse($from)->subHours($matchHours)->format('Y-m-d H:i:s');
        $extTo   = \Carbon\Carbon::parse($to)->addHours($matchHours)->format('Y-m-d H:i:s');
        $matchedCallIds = [];
        $windowSec = $matchHours * 3600;

        try {
            $b24 = DB::connection('bitrix_survey')
                ->table($b24Table)
                ->whereBetween('created_at', [$extFrom, $extTo])
                ->select('phone_number', 'created_at')
                ->get();

            foreach ($b24 as $b) {
                $norm = \App\Support\PhoneNumber::canonicalUzDigits($b->phone_number);
                if ($norm === null || empty($phoneIndex[$norm])) continue;
                $bts = strtotime($b->created_at);
                foreach ($phoneIndex[$norm] as $cc) {
                    if (abs($bts - $cc['ts']) <= $windowSec) {
                        $matchedCallIds[$cc['id']] = true;
                    }
                }
            }
        } catch (\Throwable $e) {
            // Bitrix DB unavailable — fall back silently to SMS-only count.
        }

        $withSurvey = count($matchedCallIds);
        $percent = $totalCalls > 0 ? round($withSurvey / $totalCalls * 100, 1) : 0.0;

        return Response::json([
            'count'        => $withSurvey,
            'with_survey'  => $withSurvey,
            'total_calls'  => $totalCalls,
            'percent'      => $percent,
        ]);
    }

    public function monitoringUsersFeedbacks(Request $request)
    {
        $reports = DB::table('feedback')
            ->leftJoin('calls', 'feedback.call_id', '=', 'calls.id')
            ->leftJoin('operators', 'calls.operator_id', '=', 'operators.id')
            ->select(
                DB::raw('SUM(CASE WHEN feedback.solved != 3 THEN 1 ELSE 0 END) AS mark0'),
                DB::raw('SUM(CASE WHEN feedback.solved = 3 THEN 1 ELSE 0 END) AS mark3'),
                DB::raw('SUM(CASE WHEN feedback.solved = 4 THEN 1 ELSE 0 END) AS mark4'),
                'operators.name',
                'operators.phone'
            )
            ->whereBetween('feedback.created_at', [$request->from." 00:00:00", $request->to." 23:59:59"])
            ->groupBy('calls.operator_id')
            ->get();  

        return Response::json($reports);
    }

    public function monitoringUsersTrainings(Request $request)
    {
        $reports = DB::table('trainings')
            ->select(
                DB::raw('SUM(training) AS training'),
                'operator'
            )
            ->whereBetween('date', [$request->from." 00:00:00", $request->to." 23:59:59"])
            ->groupBy('operator')
            ->get();  

        return Response::json($reports);
    }

    public function monitoringBigData(Request $request)
    {
        if ($request['date']) {
            $from = strtotime(substr($request['date'], 0, -2) . "01 00:00:00");
            $to = strtotime($request['date'] . " 23:59:59");
        }else{
            $from = strtotime($request['from'] . " 00:00:00");
            $to = strtotime($request['to'] . " 23:59:59");
        }
        $gateway = $request['gateway'] ?? '712075995';
        $excCondition = $this->timeExceptions($from, $to);
        $inner = $this->monitoringCallsSubquery($gateway);

        $calls = DB::select("SELECT * FROM ($inner) t WHERE t.start_stamp BETWEEN $from AND $to $excCondition");

        if (!empty($excCondition)) {
            $excConditionTalk = $this->timeExceptionsTalk($from, $to);
            $callsTalk = DB::select("SELECT * FROM ($inner) t WHERE $excConditionTalk");
            if (!empty($callsTalk)) {
                $calls = array_merge($calls, $callsTalk);
            }
        }

        return Response::json($this->normalizeAllCallRows($calls));
    }

    public function monitoringOperatorCondition(Request $request)
    {
        $from = strtotime($request['date'] . " 00:00:00");
        $to = strtotime($request['date'] . " 23:59:59");
        
        $calls = Operator_time::select('uid')->where('unregister', 0)->whereBetween('timestamp_reg', [$from, $to])->get()->unique('uid');

        return Response::json(['calls' => $calls]);
    }

    private function normalizeAllCallRows(array $calls): array
    {
        foreach ($calls as $call) {
            if (!is_object($call)) {
                continue;
            }

            if (property_exists($call, 'caller_id_number')) {
                $call->caller_id_number = PhoneNumber::formatUz($call->caller_id_number) ?? $call->caller_id_number;
            }

            if (property_exists($call, 'destination_number')) {
                $call->destination_number = PhoneNumber::formatUz($call->destination_number) ?? $call->destination_number;
            }
        }

        return $calls;
    }

    public function monitoringOperatorTime(Request $request)
    {
        $from = $request['from'] . " 00:00:00";
        $to = $request['to'] . " 23:59:59";
        
        $times = Operator_time::select('uid', 'timestamp_reg', 'timestamp_unreg')->whereBetween('created_at', [$from, $to])->orderBy('timestamp_reg', 'asc')->cursor();
        $array = [];
        foreach ($times as $ope) {
            $array[] = ['uid' => $ope->uid, 'in' => $ope->timestamp_reg, 'out' => $ope->timestamp_unreg === null ? time() : $ope->timestamp_unreg];
        }
        $oper_times = $this->calculate_total_time($array);

        $holidayModels = Holiday::select('date')->cursor();
        $holidays = [];
        foreach ($holidayModels as $h) {
            $holidays[] = $h['date'];
        }

        return Response::json(['oper_times' => $oper_times, 'holidays' => $holidays]);
    }

    public function monitoringUnknownClients(Request $request)
    {
        $from = $request['from'] . " 00:00:00";
        $to = $request['to'] . " 23:59:59";

        $clients = DB::table('unknown_clients')
            ->select('operator', 'direction', DB::raw('COUNT(phone) as count'))
            ->where('event', '=', 'call_end')
            ->whereBetween('created_at', [$from, $to])
            ->groupByRaw('direction, operator')
            ->get();

        return Response::json($clients);
    }

    public function monitoringPersonalMissed(Request $request)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://161.97.137.120:8441/download/v3");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json"
        ]);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'date_start' => $request->from,
            'date_end' => $request->to
        ]));

        $output = curl_exec($ch);
        if ($output === false) {
            curl_close($ch);

            return Response::json([]);
        }

        curl_close($ch);

        $decoded = json_decode($output, true);
        if (!is_array($decoded)) {
            return Response::json([]);
        }

        return Response::json($decoded);
    }

    private function getMonitoringAuthPaths(): array
    {
        return [
            base_path('configs/auth.txt'),
            public_path('configs/auth.txt'),
        ];
    }

    private function getMonitoringAuthData(bool $forceRefresh = false)
    {
        if (!$forceRefresh) {
            foreach ($this->getMonitoringAuthPaths() as $path) {
                if (!is_file($path)) {
                    continue;
                }

                $decoded = json_decode((string) file_get_contents($path));
                if (!empty($decoded->key) && !empty($decoded->key_id)) {
                    return $decoded;
                }
            }
        }

        return $this->refreshMonitoringAuthData();
    }

    private function refreshMonitoringAuthData()
    {
        $response = $this->performOnlinePbxJsonRequest(
            'https://api2.onlinepbx.ru/' . self::ONLINEPBX_DOMAIN . '/auth.json',
            ['auth_key' => self::ONLINEPBX_AUTH_KEY]
        );

        if (($response['status'] ?? null) !== '1' || empty($response['data']['key']) || empty($response['data']['key_id'])) {
            throw new \RuntimeException('Не удалось обновить токен очереди OnlinePBX.');
        }

        $encoded = json_encode($response['data'], JSON_UNESCAPED_UNICODE);
        foreach ($this->getMonitoringAuthPaths() as $path) {
            @file_put_contents($path, $encoded);
        }

        return json_decode($encoded);
    }

    private function fetchMonitoringFifo(): array
    {
        $auth = $this->getMonitoringAuthData();

        $response = $this->performOnlinePbxJsonRequest(
            'https://api2.onlinepbx.ru/' . self::ONLINEPBX_DOMAIN . '/fifo/get.json',
            ['asd' => 'asdad'],
            [
                'x-pbx-authentication: ' . $auth->key_id . ':' . $auth->key,
            ]
        );

        if (($response['isNotAuth'] ?? false) || ($response['errorCode'] ?? '') === 'API_KEY_CHECK_FAILED') {
            $auth = $this->getMonitoringAuthData(true);
            $response = $this->performOnlinePbxJsonRequest(
                'https://api2.onlinepbx.ru/' . self::ONLINEPBX_DOMAIN . '/fifo/get.json',
                ['asd' => 'asdad'],
                [
                    'x-pbx-authentication: ' . $auth->key_id . ':' . $auth->key,
                ]
            );
        }

        if (($response['status'] ?? null) !== '1' || !isset($response['data']) || !is_array($response['data'])) {
            $message = $response['comment'] ?? 'Очередь OnlinePBX временно недоступна.';
            throw new \RuntimeException($message);
        }

        return $response['data'];
    }

    private function performOnlinePbxJsonRequest(string $url, array $payload, array $headers = []): array
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge([
            'Content-Type: application/json',
        ], $headers));

        $rawResponse = curl_exec($ch);
        if ($rawResponse === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new \RuntimeException('OnlinePBX request failed: ' . $error);
        }

        curl_close($ch);

        $decoded = json_decode($rawResponse, true);
        if (!is_array($decoded)) {
            throw new \RuntimeException('OnlinePBX returned an invalid response.');
        }

        return $decoded;
    }

    public function calculate_total_time($array) 
    {
        $user_intervals = [];
        
        foreach ($array as $entry) {
            $user_intervals[$entry['uid']][] = $entry;
        }
        
        $total_times = [];
        
        foreach ($user_intervals as $uid => $intervals) {
            $merged_intervals = $this->merge_intervals($intervals);
            $total_time = 0;
            foreach ($merged_intervals as $interval) {
                $total_time += $interval['out'] - $interval['in'];
            }
            $total_times[$uid] = $total_time;
        }
        
        return $total_times;
    }
    
    public function merge_intervals($intervals) 
    {
        usort($intervals, function($a, $b) {
            return $a['in'] <=> $b['in'];
        });
    
        $merged = [];
        foreach ($intervals as $interval) {
            if (empty($merged) || end($merged)['out'] < $interval['in']) {
                $merged[] = $interval;
            } else {
                $merged[count($merged) - 1]['out'] = max(end($merged)['out'], $interval['out']);
            }
        }
        
        return $merged;
    }

    // ------------ workly data ---------------

    private function auth(): void
    {
        $data = "client_id=".self::client_id."&client_secret=".self::client_secret."&grant_type=password&username=".self::username."&password=".self::password;

        $ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, self::url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); 
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_HTTPHEADER, [
			"Content-type: application/x-www-form-urlencoded"
		]);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

		$output = curl_exec($ch);
        curl_close($ch);

        file_put_contents(self::workly_auth, $output);
    }

    private function getData($url, $token)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-type: application/x-www-form-urlencoded",
            "Authorization: Bearer $token",
        ]);

        $results = curl_exec($ch);
        curl_close($ch);

        return (array) json_decode($results);
    }

    public function worklyData(Request $request)
    {
        try {
        
            $auth = (array) json_decode(file_get_contents(self::workly_auth));
            if (empty($auth)) {
                self::auth();
                $auth = (array) json_decode(file_get_contents(self::workly_auth));
            }

            $allData = [];

            if ($auth['access_token']) {
                $data = self::getData("https://api.workly.uz/v1/reports/inouts?start_date=".$request->from."&end_date=".$request->to."&f=department&ids=17554,27081,29206", $auth['access_token']);
                if (!isset($data['items']) && $data['code']) {
                    info($data);
                    self::auth();
                    $auth = (array) json_decode(file_get_contents(self::workly_auth));
                    $data = self::getData("https://api.workly.uz/v1/reports/inouts?start_date=".$request->from."&end_date=".$request->to."&f=department&ids=17554,27081,29206", $auth['access_token']);
                }
                
                foreach ($data['items'] as $datum) {
                    $allData[] = ['id' => $datum->employee_id, 'fullname' => $datum->full_name, 'date' => $datum->event_full_date];
                }

                $pages = $data['_meta']->pageCount;
                if ($pages > 1) {
                    $next = $data['_links']->next->href;
                    $ch = curl_init();
                    for ($i=2; $i <= $pages; $i++) { 
                        
                        curl_setopt($ch, CURLOPT_URL, $next);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                        curl_setopt($ch, CURLOPT_HTTPHEADER, [
                            "Content-type: application/x-www-form-urlencoded",
                            "Authorization: Bearer ".$auth['access_token'],
                        ]);
                        $output = curl_exec($ch);
                        $data = (array) json_decode($output);

                        if ($i != $pages) {
                            $next = $data['_links']->next->href;
                        }

                        foreach ($data['items'] as $datum) {
                            $allData[] = ['id' => $datum->employee_id, 'fullname' => $datum->full_name, 'date' => $datum->event_full_date];
                        }                    
                    }
                    curl_close($ch);
                }
            }

            return Response::json($allData);

        } catch (\Throwable $th) {
            return Response::json([]);
        }

    } 
    
    public function worklyDataX($request) {
        $startTime = microtime(true); // Start timing
        $auth = (array) json_decode(file_get_contents(self::workly_auth));
        dump('Auth loading time: ' . (microtime(true) - $startTime) . ' seconds');
        
        if (empty($auth)) {
            $startAuthTime = microtime(true);
            self::auth();
            dump('Auth refresh time: ' . (microtime(true) - $startAuthTime) . ' seconds');
            $auth = (array) json_decode(file_get_contents(self::workly_auth));
        }
        $allData = [];
        
        if (isset($auth['access_token'])) {
            $currentPage = 1;
            $totalPages = 1;
        
            do {
                $pageStartTime = microtime(true); // Start timing page fetch
                
                // Fetch data for the current page
                $data = self::getData("https://api.workly.uz/v1/reports/at-work?start_date=" . $request->from . "&end_date=" . $request->to . "&page=" . $currentPage, $auth['access_token']);
                dump('Page ' . $currentPage . ' fetch time: ' . (microtime(true) - $pageStartTime) . ' seconds');
                
                if (!isset($data['items'])) {
                    info($data);
                    if (!isset($data->code)) {
                        $refreshAuthTime = microtime(true);
                        self::auth();
                        dump('Auth refresh time during loop: ' . (microtime(true) - $refreshAuthTime) . ' seconds');
                        $auth = (array) json_decode(file_get_contents(self::workly_auth));
                        continue;
                    } else {
                        break;
                    }
                }
        
                $processingStartTime = microtime(true); // Start timing data processing
                foreach ($data['items'] as $datum) {
                    if ($datum->employee->department_id != '17554') {
                        continue;
                    }
                    if (!isset($datum->scheduled->start_time) || !isset($datum->actual->first_in)) {
                        continue;
                    }
        
                    $scheduledTime = new \DateTime($datum->scheduled->start_time);
                    $realTime = new \DateTime($scheduledTime->format('Y-m-d') . ' ' . $datum->actual->first_in);
        
                    $interval = $scheduledTime->diff($realTime);
                    $late_for = ($interval->h * 60) + $interval->i;
        
                    if ($realTime < $scheduledTime) {
                        $late_for = -$late_for;
                    }
        
                    $status = ($datum->scheduled->start_time == null) ? "qo'shimcha" :
                              (($late_for > 0) ? "late" : "on_time");
        
                    $allData[] = [
                        'id' => $datum->employee->id,
                        'fullname' => $datum->employee->full_name,
                        'department_id' => $datum->employee->department_id,
                        'date' => $datum->scheduled->report_date,
                        'scheduled' => $datum->scheduled->start_time,
                        'real' => $datum->actual->first_in,
                        'time_diff' => $late_for,
                        'status' => $status
                    ];
                }
                dump('Data processing time for page ' . $currentPage . ': ' . (microtime(true) - $processingStartTime) . ' seconds');
        
                $currentPage++;
                $totalPages = isset($data['_meta']) && is_object($data['_meta']) && property_exists($data['_meta'], 'pageCount') 
                    ? $data['_meta']->pageCount 
                    : 1;
            } while ($currentPage <= $totalPages);
        }
        
        dump('Total execution time: ' . (microtime(true) - $startTime) . ' seconds');
        return response()->json($allData);
    }
    
    
    
    public function worklySchedule()
    {
        try {
        
            $auth = (array) json_decode(file_get_contents(self::workly_auth));

            $allData = [];

            if ($auth['access_token']) {
                $data = self::getData("https://api.workly.uz/v1/employees", $auth['access_token']);

                if (!$data['items'] && $data['code']) {
                    info($data);
                    self::auth();
                    $auth = (array) json_decode(file_get_contents(self::workly_auth));
                    $data = self::getData("https://api.workly.uz/v1/employees", $auth['access_token']);
                }
                
                foreach ($data['items'] as $datum) {
                    $allData[] = ['id' => $datum->id, 'fullname' => $datum->full_name, 'schedule' => $datum->schedule->title];
                }

                $pages = $data['_meta']->pageCount;
                if ($pages > 1) {
                    $next = $data['_links']->next->href;
                    $ch = curl_init();
                    for ($i=2; $i <= $pages; $i++) { 
                        
                        curl_setopt($ch, CURLOPT_URL, $next);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                        curl_setopt($ch, CURLOPT_HTTPHEADER, [
                            "Content-type: application/x-www-form-urlencoded",
                            "Authorization: Bearer ".$auth['access_token'],
                        ]);
                        $output = curl_exec($ch);
                        $data = (array) json_decode($output);

                        if ($i != $pages) {
                            $next = $data['_links']->next->href;
                        }

                        foreach ($data['items'] as $datum) {
                            $allData[] = ['id' => $datum->id, 'fullname' => $datum->full_name, 'schedule' => $datum->schedule->title];
                        }                    
                    }
                    curl_close($ch);
                }
            }

            return Response::json($allData);

        } catch (\Throwable $th) {
            return Response::json([]);
        }

    } 

    public function worklyOperators()
    {
        $opers = Operator::where('workly_id', '!=', '')->get();
        $worklyOpers = [];
        foreach ($opers as $oper) {
            $worklyOpers[$oper['phone']] = $oper['workly_id'];
        }
        return Response::json($worklyOpers);
    }

    // ------------- scores ---------------

    public function score()
    {
        $scores = Score::get();
        $score = [];
        foreach ($scores as $s) {
            $score[$s['key_text']] = json_decode($s['value']);
        }
        return Response::json($score);
    }
}
