<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddGatewayToWorkHours extends Migration
{
    public function up()
    {
        // Composite PK migration: (gateway, weekday). The "default" row uses gateway=''.
        DB::statement("ALTER TABLE work_hours
            DROP PRIMARY KEY,
            ADD COLUMN gateway VARCHAR(20) NOT NULL DEFAULT '' AFTER weekday,
            ADD PRIMARY KEY (gateway, weekday)");

        // Seed per-gateway rows = a copy of the default config for SD / Ibox / iDokon.
        $defaults = DB::table('work_hours')->where('gateway', '')->get();
        $now      = now();
        $rows     = [];
        foreach (['712075995', '781138585', '781136022'] as $gw) {
            foreach ($defaults as $d) {
                $rows[] = [
                    'gateway'    => $gw,
                    'weekday'    => $d->weekday,
                    'start_hour' => $d->start_hour,
                    'end_hour'   => $d->end_hour,
                    'is_active'  => $d->is_active,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }
        if (!empty($rows)) {
            DB::table('work_hours')->insert($rows);
        }
    }

    public function down()
    {
        DB::table('work_hours')->where('gateway', '!=', '')->delete();
        DB::statement("ALTER TABLE work_hours
            DROP PRIMARY KEY,
            DROP COLUMN gateway,
            ADD PRIMARY KEY (weekday)");
    }
}
