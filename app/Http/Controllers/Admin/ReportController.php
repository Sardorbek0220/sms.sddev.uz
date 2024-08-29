<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Response;
use App\All_call;
use App\Operators;
use App\Feedback;
use App\Operator_time;

class ReportController extends Controller
{
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

        // $times = Operator_time::where('ip', '!=', '185.209.112.43')->whereBetween('created_at', [$from_date." 00:00:00", $to_date." 23:59:59"])->orderBy('timestamp', 'asc')->cursor();
        // $oper_times = [];
        // foreach ($times as $time) {
        //     $oper_times[$time['uid']][$time['status']][] = $time['timestamp'];
        //     if ($time['status'] == 'unregister') {

        //         if (!array_key_exists('online_time', $oper_times[$time['uid']])) {
        //             $oper_times[$time['uid']]['online_time'] = 0;
        //         }
                
        //         $index = count($oper_times[$time['uid']]['register'])-1;
        //         $oper_times[$time['uid']]['online_time'] += ($time['timestamp'] - $oper_times[$time['uid']]['register'][$index]);
        //     }
        // }
        // dd($oper_times);

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
        $calls = All_call::where('start_stamp', '>', $request['from'])->where('start_stamp', '<', $request['to'])->get();

        return Response::json($calls);
    }

    public function monitoringUsers(Request $request)
    {
        $users = DB::table('operators')->select('name', 'phone as num')->get();

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
        $from = strtotime(substr($request['date'], 0, -2) . "01 00:00:00");
        $to = strtotime($request['date'] . " 23:59:59");
        
        $calls = All_call::whereBetween('start_stamp', [$from, $to])->cursor();

        return Response::json($calls);
    }

    public function monitoringOperatorCondition(Request $request)
    {
        $from = strtotime($request['date'] . " 00:00:00");
        $to = strtotime($request['date'] . " 23:59:59");
        
        $calls = Operator_time::select('uid', 'status')->where('ip', '!=', '185.209.112.43')->whereBetween('timestamp', [$from, $to])->orderBy('timestamp', 'desc')->get()->unique('uid');

        return Response::json($calls);
    }

}
