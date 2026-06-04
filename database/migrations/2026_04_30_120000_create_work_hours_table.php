<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateWorkHoursTable extends Migration
{
    public function up()
    {
        Schema::create('work_hours', function (Blueprint $table) {
            $table->tinyInteger('weekday')->primary();
            $table->tinyInteger('start_hour')->default(9);
            $table->tinyInteger('end_hour')->default(18);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        $defaults = [
            ['weekday' => 0, 'start_hour' => 9,  'end_hour' => 18, 'is_active' => 1],
            ['weekday' => 1, 'start_hour' => 9,  'end_hour' => 20, 'is_active' => 1],
            ['weekday' => 2, 'start_hour' => 9,  'end_hour' => 20, 'is_active' => 1],
            ['weekday' => 3, 'start_hour' => 9,  'end_hour' => 20, 'is_active' => 1],
            ['weekday' => 4, 'start_hour' => 9,  'end_hour' => 20, 'is_active' => 1],
            ['weekday' => 5, 'start_hour' => 9,  'end_hour' => 20, 'is_active' => 1],
            ['weekday' => 6, 'start_hour' => 9,  'end_hour' => 18, 'is_active' => 1],
        ];

        $now = now();
        foreach ($defaults as &$row) {
            $row['created_at'] = $now;
            $row['updated_at'] = $now;
        }
        DB::table('work_hours')->insert($defaults);
    }

    public function down()
    {
        Schema::dropIfExists('work_hours');
    }
}
