<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Work-hours configuration. Composite PK = (gateway, weekday).
 * gateway = '' means "default for any gateway not configured explicitly".
 * Currently we seed per-gateway rows for Sales Doctor (712075995), Ibox (781138585), iDokon (781136022).
 */
class WorkHour extends Model
{
    protected $table = 'work_hours';
    public $incrementing = false;

    protected $fillable = [
        'gateway', 'weekday', 'start_hour', 'end_hour', 'is_active',
    ];

    protected $casts = [
        'weekday'    => 'int',
        'start_hour' => 'int',
        'end_hour'   => 'int',
        'is_active'  => 'bool',
    ];

    public const GATEWAYS = [
        ''          => 'По умолчанию (для всех остальных)',
        '712075995' => 'Sales Doctor (712075995)',
        '781138585' => 'Ibox (781138585)',
        '781136022' => 'iDokon (781136022)',
    ];

    public static function defaultMap(): array
    {
        return [
            0 => ['start_hour' => 9, 'end_hour' => 18, 'is_active' => true],
            1 => ['start_hour' => 9, 'end_hour' => 20, 'is_active' => true],
            2 => ['start_hour' => 9, 'end_hour' => 20, 'is_active' => true],
            3 => ['start_hour' => 9, 'end_hour' => 20, 'is_active' => true],
            4 => ['start_hour' => 9, 'end_hour' => 20, 'is_active' => true],
            5 => ['start_hour' => 9, 'end_hour' => 20, 'is_active' => true],
            6 => ['start_hour' => 9, 'end_hour' => 18, 'is_active' => true],
        ];
    }

    /**
     * Returns map [gateway => [weekday => [start,end,active]]].
     * Always includes all 4 known gateway keys (default + 3 companies),
     * filling in missing rows with the default-config map.
     */
    public static function fullMap(): array
    {
        $blank = self::defaultMap();
        $out = [];
        foreach (array_keys(self::GATEWAYS) as $gw) {
            $out[$gw] = $blank; // start with defaults
        }
        $rows = DB::table('work_hours')->get();
        foreach ($rows as $r) {
            $gw = (string) $r->gateway;
            if (!isset($out[$gw])) $out[$gw] = $blank;
            $out[$gw][(int) $r->weekday] = [
                'start_hour' => (int) $r->start_hour,
                'end_hour'   => (int) $r->end_hour,
                'is_active'  => (bool) $r->is_active,
            ];
        }
        return $out;
    }

    /** Resolve config for a specific (gateway, weekday) with fallback to default. */
    public static function lookup(string $gateway, int $weekday): array
    {
        $row = DB::table('work_hours')
            ->where('gateway', $gateway)
            ->where('weekday', $weekday)
            ->first();
        if ($row) {
            return [
                'start_hour' => (int) $row->start_hour,
                'end_hour'   => (int) $row->end_hour,
                'is_active'  => (bool) $row->is_active,
            ];
        }
        // Fallback to default gateway row.
        $row = DB::table('work_hours')
            ->where('gateway', '')
            ->where('weekday', $weekday)
            ->first();
        if ($row) {
            return [
                'start_hour' => (int) $row->start_hour,
                'end_hour'   => (int) $row->end_hour,
                'is_active'  => (bool) $row->is_active,
            ];
        }
        return self::defaultMap()[$weekday] ?? ['start_hour' => 9, 'end_hour' => 18, 'is_active' => true];
    }
}
