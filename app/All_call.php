<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class All_call extends Model
{
    protected $fillable = [
        'id',
        'uuid',
        'caller_id_name',
        'caller_id_number',
        'destination_number',
        'start_stamp',
        'end_stamp',
        'duration',
        'user_talk_time',
        'accountcode',
        'gateway',
    ];

}
