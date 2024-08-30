<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Operator_time extends Model
{
    protected $fillable = [
        'id',
        'uid',
        'operator_id',
        'status',
        'timestamp',
        'ip',
        'port',
        'created_at',
        'updated_at'
    ];

}
