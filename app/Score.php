<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Score extends Model
{
    protected $fillable = [
        'id',
        'key_text',
        'value',
        'from_date',
        'to_date',
        'created_at',
        'updated_at',
    ];

    const keys = [
        'workly_ontime' => 'Workly (вовремя)',
        'workly_late' => 'Workly (поздно)',
        'personal_missed' => 'Перс. пропущ. звон',
        'missed' => 'Пропущенные в раб. время',
        'inbound' => 'Вход. звон',
        'total_feedback' => 'Total feedback',
        'mark3_feedback' => 'Mark3 feedback',
        'like' => 'Like',
        'punishment' => 'Punishment',
        'unreg_client_inbound' => 'Незарег. вход. клиенты',
        'online_time' => 'Онлайн время'
    ];
}
