<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\WorkHour;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WorkHoursController extends Controller
{
    private const WEEKDAYS = [
        1 => 'Понедельник',
        2 => 'Вторник',
        3 => 'Среда',
        4 => 'Четверг',
        5 => 'Пятница',
        6 => 'Суббота',
        0 => 'Воскресенье',
    ];

    public function index()
    {
        return view('admin.work_hours.index', [
            'gateways'      => WorkHour::GATEWAYS,
            'fullMap'       => WorkHour::fullMap(),
            'weekdayLabels' => self::WEEKDAYS,
        ]);
    }

    public function update(Request $request)
    {
        $allGateways = array_keys(WorkHour::GATEWAYS);
        $payload = $request->input('hours', []);
        if (!is_array($payload)) {
            return redirect()->back()->with('error', 'Некорректные данные');
        }

        DB::transaction(function () use ($payload, $allGateways) {
            foreach ($allGateways as $gw) {
                $gwRows = $payload[$gw] ?? [];
                if (!is_array($gwRows)) continue;
                foreach (range(0, 6) as $weekday) {
                    $entry  = $gwRows[$weekday] ?? [];
                    $start  = (int) ($entry['start_hour'] ?? 9);
                    $end    = (int) ($entry['end_hour']   ?? 18);
                    $active = !empty($entry['is_active']);
                    if ($start < 0 || $start > 23) $start = 9;
                    if ($end   < 1 || $end   > 24) $end   = 18;
                    if ($end <= $start) $end = min(24, $start + 1);

                    DB::table('work_hours')->updateOrInsert(
                        ['gateway' => (string) $gw, 'weekday' => $weekday],
                        [
                            'start_hour' => $start,
                            'end_hour'   => $end,
                            'is_active'  => $active ? 1 : 0,
                            'updated_at' => now(),
                            'created_at' => now(),
                        ]
                    );
                }
            }
        });

        return redirect()->route('admin.work-hours')->with('success', 'Сохранено');
    }

    public function asJson()
    {
        return response()->json([
            'gateways' => WorkHour::GATEWAYS,
            'hours'    => WorkHour::fullMap(),
        ]);
    }
}
