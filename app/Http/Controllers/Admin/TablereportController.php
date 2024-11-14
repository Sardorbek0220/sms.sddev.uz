<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Product;
use App\Like;
use Carbon\Carbon; // Ensure this is included at the top of your controller
use App\Http\Controllers\Admin\ReportController;
use App\Operator_time;

class TablereportController extends Controller
{

    public function GetOperators(){
        
        $auth = json_decode(file_get_contents("configs/auth.txt"));
        $key_and_id = $auth->key_id.":".$auth->key;
        
        // Define the API URL and data
        $url = "https://api2.onlinepbx.ru/pbx12127.onpbx.ru/fifo/get.json";
        $postData = [
            'asd' => 'asdad'
        ];
    
        // Initialize cURL
        $ch = curl_init($url);
    
        // Set cURL options
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
            "x-pbx-authentication: {$key_and_id}"
        ]);
    
        // Execute the request and decode the JSON response
        $response = curl_exec($ch);
        $responseData = json_decode($response, true);
    
        // Close cURL
        curl_close($ch);
        //dump($responseData);
        // Check for a successful response
        if (isset($responseData['data'])) {
            return $responseData['data'];  // return or process the FIFO data
        } else {
            // Handle the error as needed
            return null;
        }        
    }

    public function index(Request $request)
    {
        // Set default date range
        $from_date = Carbon::parse($request->from_date ?? date('Y-m-d'));
        $to_date = Carbon::parse($request->to_date ?? date('Y-m-d'));

        // Fetch operators from JSON file
        $users = file_get_contents('configs/pbx_users.json');
        $operators = (array) json_decode($users)->users;
        // Fetch products within the specified date range
        $products = Product::whereBetween('date', [$from_date . " 00:00:00", $to_date . " 23:59:59"])
        ->get(); // Get the results from the query
        //dump($products);

        // Debugging: Dump the products to see the output
        
        // Calculate averages per operator per day
        $averages = [];
        
        foreach ($products as $item) {
            $date = Carbon::parse($item->date)->format('Y-m-d'); // Extract date
            $operator = $item->operator;
            $score = $item->product; // Assuming the score is stored in the 'product' field
            
            // Initialize the array if not set
            if (!isset($averages[$operator])) {
                $averages[$operator] = [];
            }
            
            // Initialize the date array if not set
            if (!isset($averages[$operator][$date])) {
                $averages[$operator][$date] = [
                    'total' => 0,
                    'count' => 0,
                ];
            }
            
            // Accumulate score and count
            $averages[$operator][$date]['total'] += $score;
            $averages[$operator][$date]['count']++;
        }
        //dump($averages);
        
        // Calculate the final averages
        foreach ($averages as $operator => $dates) {
            $totalScore = 0; // Initialize total score for the operator
            $totalCount = 0; // Initialize total count for the operator

            foreach ($dates as $date => $data) {
                // Calculate daily average
                $averages[$operator][$date] = $data['count'] > 0 ? $data['total'] / $data['count'] : 0; // Avoid division by zero

                // Accumulate totals for overall average
                $totalScore += $data['total'];
                $totalCount += $data['count'];
            }

            // Calculate overall average and store it
            $averages[$operator]['Total'] = $totalCount > 0 ? $totalScore / $totalCount : 0; // Avoid division by zero
        }

        // Sort the averages by the "Total" column in descending order
        uasort($averages, function ($a, $b) {
            return $b['Total'] <=> $a['Total']; // Compare Total values
        });
        // Get the sorted operator IDs
        $sortedOperatorIds = array_keys($averages);

        // Rebuild the $operators array in the same order
        $sortedOperators = [];
        foreach ($sortedOperatorIds as $id) {
            // Ensure to fetch the operator name from the original $operators array
            if (isset($operators[$id])) {
                $sortedOperators[$id] = $operators[$id];
            }
        }
        // Calculate averages per operator per day
        $averages_script = [];
        
        foreach ($products as $item) {
            $date = Carbon::parse($item->date)->format('Y-m-d'); // Extract date
            $operator = $item->operator;
            $score = $item->script; // Assuming the score is stored in the 'product' field
            
            // Initialize the array if not set
            if (!isset($averages_script[$operator])) {
                $averages_script[$operator] = [];
            }
            
            // Initialize the date array if not set
            if (!isset($averages_script[$operator][$date])) {
                $averages_script[$operator][$date] = [
                    'total' => 0,
                    'count' => 0,
                ];
            }
            
            // Accumulate score and count
            $averages_script[$operator][$date]['total'] += $score;
            $averages_script[$operator][$date]['count']++;
        }
        //dump($averages_script);
        
        // Calculate the final averages
        foreach ($averages_script as $operator => $dates) {
            $totalScore = 0; // Initialize total score for the operator
            $totalCount = 0; // Initialize total count for the operator

            foreach ($dates as $date => $data) {
                // Calculate daily average
                $averages_script[$operator][$date] = $data['count'] > 0 ? $data['total'] / $data['count'] : 0; // Avoid division by zero

                // Accumulate totals for overall average
                $totalScore += $data['total'];
                $totalCount += $data['count'];
            }

            // Calculate overall average and store it
            $averages_script[$operator]['Total'] = $totalCount > 0 ? $totalScore / $totalCount : 0; // Avoid division by zero
        }

        // Sort the averages by the "Total" column in descending order
        uasort($averages_script, function ($a, $b) {
            return $b['Total'] <=> $a['Total']; // Compare Total values
        });
        // Get the sorted operator IDs
        $sortedOperatorIds = array_keys($averages_script);

        // Rebuild the $operators array in the same order
        $scriptOperators = [];
        foreach ($sortedOperatorIds as $id) {
            // Ensure to fetch the operator name from the original $operators array
            if (isset($operators[$id])) {
                $scriptOperators[$id] = $operators[$id];
            }
        }

        # LIKES
        $likes = Like::whereBetween('date', [$from_date." 00:00:00", $to_date." 23:59:59"])->get();
        
        $table_punishment = [];
        $table_likes = [];
        #dump((array) json_decode($users)->users);
        $ids = (array) json_decode($users)->users;  // Decode the JSON and cast it to an array
        $keys = array_keys($ids);
        foreach($keys as $operator){
            // Initialize the array if not set
            if (!isset($table_likes[$operator])) {
                $table_likes[$operator] = [];
                $table_likes[$operator] = [
                    'total' =>0,
                ];
                
            }
    
            // Initialize the array if not set
            if (!isset($table_punishment[$operator])) {
                $table_punishment[$operator] = [];
                $table_punishment[$operator] = [
                    'total' =>0,
                ];
                
            }
        }
        
        foreach ($likes as $item) {
            $date = Carbon::parse($item->date)->format('Y-m-d'); // Extract date
            $operator = $item->operator;
            $punishment = $item->punishment; // Assuming the score is stored in the 'product' field
            

            // Initialize the date array if not set
            if (!isset($table_likes[$operator][$date])) {
                $table_likes[$operator][$date] = 0;
            }
            
            // Initialize the date array if not set
            if (!isset($table_punishment[$operator][$date])) {
                $table_punishment[$operator][$date] = 0;
            }
            
            if($punishment === 1){
                // Accumulate score and count
                $table_punishment[$operator][$date] -= 1;
                $table_punishment[$operator]['total'] -= 1;
            }else{
                $table_likes[$operator][$date] += 1;
                $table_likes[$operator]['total'] += 1;
            }
        }
        #dump($table_likes);
        
        // Sort the averages by the "Total" column in descending order
        uasort($table_likes, function ($a, $b) {
            return $b['total'] <=> $a['total']; // Compare Total values
        });
        // Get the sorted operator IDs
        $sortedOperatorIds = array_keys($table_likes);

        // Rebuild the $operators array in the same order
        $likeOperators = [];
        foreach ($sortedOperatorIds as $id) {
            // Ensure to fetch the operator name from the original $operators array
            if (isset($operators[$id])) {
                $likeOperators[$id] = $operators[$id];
            }
        }

        
        // Sort the averages by the "Total" column in descending order
        uasort($table_punishment, function ($a, $b) {
            return $b['total'] <=> $a['total']; // Compare Total values
        });
        // Get the sorted operator IDs
        $sortedOperatorIds = array_keys($table_punishment);

        // Rebuild the $operators array in the same order
        $punishmentOperators = [];
        foreach ($sortedOperatorIds as $id) {
            // Ensure to fetch the operator name from the original $operators array
            if (isset($operators[$id])) {
                $punishmentOperators[$id] = $operators[$id];
            }
        }

        //ReportController::auth();
        //dump($from_date."");
        $req = new Request([
            'from' => date('Y-m-d', strtotime($from_date."")),
            'to' => date('Y-m-d', strtotime($to_date.""))
        ]);
        


        $reportController = new \App\Http\Controllers\Admin\ReportController();
        $res = $reportController->worklyDataX($req);
        $data = $res->original;
        
        $filteredData = array_filter($data, function($item) {
            return isset($item['department_id']) && $item['department_id'] == 17554;
        });
        
        // Extract unique 'id' values from the filtered array
        $worklyID = array_unique(array_column($filteredData, 'id'));

        // Step 1: Get unique dates to use as column headers
        $dates = array_unique(array_column($data, 'date'));
        $worklyID = array_unique(array_column($filteredData, 'id'));
        sort($dates); // Sort dates if necessary

        // Step 2: Initialize the pivot structure
        $pivot = [];

        // Step 3: Populate pivot with each entry grouped by employee
        foreach ($data as $entry) {
            
            $id = $entry['id'];
            $name = $entry['fullname'];
            $date = $entry['date'];
            $department_id = $entry['department_id'];
            if($department_id == 17554){

                
                // Ensure row for this employee exists
                if (!isset($pivot[$id])) {
                    $pivot[$id] = ['id' => $id, 'fullname' => $name, 'department_id' => $department_id];
                }
                
                // Set the values under the date column as an array of details
                $pivot[$id][$date] = [
                    'scheduled' => $entry['scheduled'],
                    'real' => $entry['real'],
                    'time_diff' => $entry['time_diff'],
                    'status' => $entry['status'],
                ];
            }
        }

        // Step 4: Fill missing dates with null values for each employee
        foreach ($pivot as &$row) {
            foreach ($dates as $date) {
                if (!isset($row[$date])) {
                    $row[$date] = null; // Fill with null or default value
                }
            }
        }
        $data = $reportController->monitoringPersonalMissed($req);
        $data = $data->original;
        
        $fifos = $this->GetOperators();
        $users =  $reportController->monitoringUsers($req);
        $users = $users->original;  
        //$usersArr = $users;
        $usersArray = $users->toArray();
        // Initialize an empty array to hold the transformed data
        $userJsonArray = [];

        // Transform the array
        foreach ($usersArray as $user) {
            $userJsonArray[$user->num] = [
                'name' => $user->name,
                'field' => $user->field,
            ];
        }


        //dump($userJsonArray);
        $userArray = explode(';', $fifos[0]['users']);
        $userArray = array_diff($userArray, array("120"));
        $userArray = array_values($userArray);

    
        //dump($data);
        //dump($data);
        $missed = [];

        foreach($data as $call) {
            $create = \DateTime::createFromFormat(\DateTime::RFC1123, $call->create_timestamp);
            $destroy = \DateTime::createFromFormat(\DateTime::RFC1123, $call->destroy_timestamp);
        
            // Ensure $create and $destroy were parsed successfully
            if ($create && $destroy) {
                
                if (
                    in_array($call->destination_number, $userArray) &&
                    !in_array($call->caller_number, $userArray) &&
                    strpos($call->caller_number, ".onpbx.ru") === false &&
                    abs($destroy->getTimestamp() - $create->getTimestamp()) >= 4
                ) {
                    $missed[] = $call;
                }
            } else {
                // Handle parsing errors, e.g., log or skip this call
                continue;
            }
        }

        $groupedData = [];
        foreach($userArray as $tmp){
            // Initialize the group if it doesn't exist
            if (!isset($groupedData["Total"][$tmp])) {
                $groupedData["Total"][$tmp] = 0;
            }
        }

        foreach ($missed as $call) {
            // Extract the date part (e.g., "2024-11-01") from create_timestamp
            $date = \DateTime::createFromFormat(\DateTime::RFC1123, $call->create_timestamp)->format('Y-m-d');
            $destinationNumber = $call->destination_number;

            // Initialize the group if it doesn't exist
            if (!isset($groupedData[$date][$destinationNumber])) {
                $groupedData[$date][$destinationNumber] = 0;
            }

            // Increment the count of uuid for the date and destination number group
            $groupedData[$date][$destinationNumber]++;
            $groupedData["Total"][$destinationNumber]++;
        }
        //dump($missed);
        asort($groupedData["Total"]);

        // Get array of destination numbers in descending order of total count
        $sortedDestinationNumbers = array_keys($groupedData["Total"]);
        //dump($groupedData);

        $from = $req['from'] . " 00:00:00";
        $to = $req['to'] . " 23:59:59";
        $clients = DB::table('unknown_clients')
            ->select(
                DB::raw('DATE(created_at) as date'),
                'operator',
                'direction',
                DB::raw('COUNT(phone) as count')
            )
            ->where('event', '=', 'call_end')
            ->whereBetween('created_at', [$from, $to])
            ->groupByRaw('DATE(created_at), direction, operator')
            ->get();

        $unregData = [];

        foreach($userArray as $tmp){
            // Initialize the group if it doesn't exist
            // Ensure the operator and date are properly structured in the pivot array
            if (!isset($unregData[$tmp])) {
                $unregData[$tmp] = [];
                $unregData[$tmp]['Total'] = [];
            }
            if (!isset($unregData[$tmp]['Total']['all'])){
                $unregData[$tmp]['Total']['all'] = 0;
            }
        }

        // Process each row in the raw data
        foreach ($clients as $r) {
            $operatorUn = $r->operator;
            $dateUn = $r->date;
            $directionUn = $r->direction;
            $countUn = $r->count;
            if (in_array($operatorUn, $userArray)) {
                // Code to execute if $operatorUn is in $userArray
                if (!isset($unregData[$operatorUn]["Total"][$directionUn])) {
                    $unregData[$operatorUn]['Total'][$directionUn] = 0;
                }
                
                // Organize data by date and direction under each operator
                $unregData[$operatorUn][$dateUn][$directionUn] = $countUn;
                $unregData[$operatorUn]["Total"][$directionUn] += $countUn;
                $unregData[$operatorUn]['Total']['all'] += $countUn;
            }
        }
        
        // Sort $unregData by the 'Total'->'all' count in descending order while preserving keys
        uasort($unregData, function ($a, $b) {
            return $a['Total']['all'] <=> $b['Total']['all'];
        });

        $times = Operator_time::select('uid', 'timestamp_reg', 'timestamp_unreg')->whereBetween('created_at', [$from, $to])->orderBy('timestamp_reg', 'asc')->cursor();
        $array = [];
        foreach ($times as $ope) {
            $array[] = ['uid' => $ope->uid, 'in' => $ope->timestamp_reg, 'out' => $ope->timestamp_unreg === null ? time() : $ope->timestamp_unreg];
        }
        
        $onlineTime = [];
        
        foreach($userArray as $tmp){
            // Initialize the group if it doesn't exist
            // Ensure the operator and date are properly structured in the pivot array
            if (!isset($onlineTime[$tmp])) {
                $onlineTime[$tmp] = [];
                $onlineTime[$tmp]["Total"] = 0;
            }
        }


        // Process each entry in the array
        foreach ($array as $entry) {
            $uid = $entry['uid'];
            if (in_array($uid, $userArray)) {

                $inTime = $entry['in'];
                $outTime = $entry['out'];
                
                // Convert the 'in' time to a date (you can adjust the timezone if needed)
                $date = date('Y-m-d', $inTime);

                if (!isset($onlineTime[$uid][$date])) {
                    $onlineTime[$uid][$date] = 0;
                }
                
                // Sum the 'in' time for this uid and date
                $onlineTime[$uid][$date] += $outTime - $inTime;
                $onlineTime[$uid]["Total"] += $outTime - $inTime;
            }
        }

        // Sort $unregData by the 'Total'->'all' count in descending order while preserving keys
        uasort($onlineTime, function ($a, $b) {
            return $b['Total'] <=> $a['Total'];
        });

        //dump($onlineTime);

        $reports = DB::table('feedback')
            ->leftJoin('calls', 'feedback.call_id', '=', 'calls.id')
            ->leftJoin('operators', 'calls.operator_id', '=', 'operators.id')
            ->select(
                DB::raw('SUM(CASE WHEN feedback.solved = 0 THEN 1 ELSE 0 END) AS mark0'),
                DB::raw('SUM(CASE WHEN feedback.solved = 1 THEN 1 ELSE 0 END) AS mark1'),
                DB::raw('SUM(CASE WHEN feedback.solved = 2 THEN 1 ELSE 0 END) AS mark2'),
                DB::raw('SUM(CASE WHEN feedback.solved = 3 THEN 1 ELSE 0 END) AS mark3'),
                'operators.name',
                DB::raw('DATE(feedback.created_at) AS date') // Extract only the date part
            )
            ->whereBetween('feedback.created_at', [$from_date." 00:00:00", $to_date." 23:59:59"])
            ->groupBy('calls.operator_id', DB::raw('DATE(feedback.created_at)')) // Group by operator and date
            ->get();

        $marks = [];

        foreach ($reports as $report) {
            $name = $report->name;
            $date = $report->date;
        
            if (!isset($marks[$name])) {
                $marks[$name] = ['name' => $name];
                $marks[$name]["TotalCount"] = 0;
                $marks[$name]["TotalMark3"] = 0;
            }

            $cnt = $report->mark0+$report->mark1+$report->mark2+$report->mark3;

            
            $marks[$name]["TotalCount"] += $cnt;
            $marks[$name]["TotalMark3"] += $report->mark3;
        
            // Aggregate marks for each date as needed
            $marks[$name][$date] = [
                'count' => $cnt,
                'mark0' => $report->mark0,
                'mark1' => $report->mark1,
                'mark2' => $report->mark2,
                'mark3' => $report->mark3,
            ];
        }
        dump($marks);

        uasort($marks, function ($a, $b) {
            return $b['TotalCount'] <=> $a['TotalCount'];
        });

        $marksCount = $marks;

        uasort($marks, function ($a, $b) {
            return $b['TotalMark3'] <=> $a['TotalMark3'];
        });

        



        // Pass data to the view
        return view('admin.tablereport', compact(
            'sortedOperators', 'averages', 'from_date', 'to_date', 
            'scriptOperators', 'averages_script', 
            'table_likes', 'table_punishment', 
            'likeOperators', 'punishmentOperators',
            "worklyID", "pivot",
            "sortedDestinationNumbers", "groupedData", "userJsonArray",
            "unregData",
            "onlineTime",
            "marks", "marksCount"
        ));
    }
}
