<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Score extends Model
{
    protected $fillable = [
        'id',
        'key_text',
        'value',
        'created_at',
        'updated_at',
    ];

    const keys = [
        'workly' => 'Workly',
        'personal_missed' => 'Перс. пропущ. звон',
        'missed' => 'Пропущенные',
        'inbound' => 'Вход. звон',
        'total_feedback' => 'Total feedback %',
        'mark3_feedback' => 'Mark3 feedback %',
        'like' => 'Like',
        'product' => 'Punishment',
        'unreg_client_inbound' => 'Незарег. вход. клиенты',
        'script' => 'Script',
        'product' => 'Product',
        'online_time' => 'Онлайн время'
    ];
}
