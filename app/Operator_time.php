<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Operator_time extends Model
{
    protected $fillable = [
        'id',
        'uid',
        'operator_id',
        'register',
        'unregister',
        'timestamp_reg',
        'timestamp_unreg',
        'ip',
        'port',
        'created_at',
        'updated_at'
    ];

}
