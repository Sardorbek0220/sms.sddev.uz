<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Response;
use App\All_call;
use App\Operator;
use App\Feedback;
use App\Operator_time;
use App\Unknown_client;
use App\Score;
use App\Exception;

class ReportController extends Controller
{
    const url = "https://api.workly.uz/v1/oauth/token";
    const client_id = "d63626d920a18be0ce20fa5ea6768b2c662a17dfd3b93";
    const client_secret = "8988c625e95402bb7eba5cc622247d68662a17dfd3b99";
    const username = "oybek.mirkasimov@gmail.com";
    const password = "P@ssw0rd";
    const workly_auth = 'configs/workly_auth.json';

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

    public function monitoring()
    {
        $auth = json_decode(file_get_contents("configs/auth.txt"));
        $key_and_id = $auth->key_id.":".$auth->key;

        $auth_key = "OGV3MWNuVkw0VWJuZHc3c1lUeFViaWVJYnA5UXdGaXM";

        return view('admin.monitoring', compact('key_and_id', 'auth_key'));
    }

    public function monitoringData(Request $request)
    {
        $from = $request['from'];
        $to = $request['to'];

        $excCondition = $this->timeExceptions($from, $to);

        $calls = DB::select("SELECT * FROM all_calls WHERE start_stamp BETWEEN $from AND $to $excCondition");
        // $calls = All_call::where('start_stamp', '>', $request['from'])->where('start_stamp', '<', $request['to'])->get();

        return Response::json($calls);
    }

    public function monitoringUsers(Request $request)
    {
        $users = DB::table('operators')->select('name', 'phone as num', 'field')->get();

        return Response::json($users);
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

    public function monitoringBigData(Request $request)
    {
        if ($request['date']) {
            $from = strtotime(substr($request['date'], 0, -2) . "01 00:00:00");
            $to = strtotime($request['date'] . " 23:59:59");
        }else{
            $from = strtotime($request['from'] . " 00:00:00");
            $to = strtotime($request['to'] . " 23:59:59");
        }

        $excCondition = $this->timeExceptions($from, $to);
        
        $calls = DB::select("SELECT * FROM all_calls WHERE start_stamp BETWEEN $from AND $to $excCondition");
        // $calls = All_call::whereBetween('start_stamp', [$from, $to])->cursor();

        return Response::json($calls);
    }

    public function monitoringOperatorCondition(Request $request)
    {
        $from = strtotime($request['date'] . " 00:00:00");
        $to = strtotime($request['date'] . " 23:59:59");
        
        $calls = Operator_time::select('uid')->where('unregister', 0)->whereBetween('timestamp_reg', [$from, $to])->get()->unique('uid');

        return Response::json(['calls' => $calls]);
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

        return Response::json(['oper_times' => $oper_times]);
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
        
		// $ch = curl_init();

		// curl_setopt($ch, CURLOPT_URL, "https://161.97.137.120:8441/download/v3");
		// curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		// curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // Disable SSL verification
		// curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0); // Disable host verification
		// curl_setopt($ch, CURLOPT_HTTPHEADER, [
		// 	"Content-Type: application/json"
		// ]);
		// curl_setopt($ch, CURLOPT_POST, 1);
		// curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
		// 	'date_start' => $request->from,
        //     'date_end' => $request->to
		// ]));

		// $output = curl_exec($ch);

		// if ($output === false) {
        //     return Response::json(['error' => curl_error($ch)]);
		// }
		// curl_close($ch);

        // return Response::json(json_decode($output));
        return [];
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