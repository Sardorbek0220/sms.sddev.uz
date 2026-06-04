<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreatePermissionsTables extends Migration
{
    public function up()
    {
        Schema::create('permissions', function (Blueprint $table) {
            $table->string('permission_key', 60)->primary();
            $table->string('label', 150);
            $table->string('category', 50)->default('general');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('user_permissions', function (Blueprint $table) {
            $table->bigInteger('user_id')->unsigned();
            $table->string('permission_key', 60);
            $table->unsignedBigInteger('granted_by')->nullable();
            $table->timestamp('granted_at')->useCurrent();
            $table->primary(['user_id', 'permission_key']);
            $table->index('permission_key');
        });

        $now = now();
        $seed = [
            ['view_dashboard',         'Дашборд',                   'main',     10],
            ['view_monitoring',        'Мониторинг',                'main',     20],
            ['view_calls',             'Журнал звонков',            'reports',  30],
            ['view_callback_analytics','Аналитика звонков',         'reports',  40],
            ['view_feedback',          'Отзывы клиентов',           'reports',  50],
            ['view_survey_reports',    'Отчёт по анкетам',          'reports',  60],
            ['view_bigreport',         'Автоматизация / бигрепорт', 'reports',  70],
            ['view_operators',         'Операторы',                 'people',   80],
            ['view_users',             'Пользователи',              'people',   90],
            ['view_holidays',          'Праздники',                 'settings', 100],
            ['view_work_hours',        'Рабочие часы',              'settings', 110],
            ['view_survey_settings',   'Настройки анкеты',          'settings', 120],
            ['view_audit_log',         'Журнал действий',           'settings', 130],
            ['view_access_control',    'Управление доступами',      'settings', 140],
        ];
        $rows = [];
        foreach ($seed as [$k, $l, $cat, $ord]) {
            $rows[] = [
                'permission_key' => $k,
                'label'          => $l,
                'category'       => $cat,
                'sort_order'     => $ord,
                'created_at'     => $now,
                'updated_at'     => $now,
            ];
        }
        DB::table('permissions')->insert($rows);
    }

    public function down()
    {
        Schema::dropIfExists('user_permissions');
        Schema::dropIfExists('permissions');
    }
}
