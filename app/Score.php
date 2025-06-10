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
        'outbound' => 'Исход. звон',
        'total_feedback' => 'Все отзывы',
        'mark3_feedback' => 'Mark3 отзыв',
        'like' => 'Нравится',
        'punishment' => 'Наказание',
        'unreg_client_inbound' => 'Незарег. вход. клиенты',
        'online_time' => 'Онлайн время'
    ];
}
