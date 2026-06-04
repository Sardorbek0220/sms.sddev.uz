<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Exception extends Model
{
    protected $fillable = [
        'id',
        'from_exc',
        'to_exc',
        'day',
        'created_at',
        'updated_at'
    ];
}
