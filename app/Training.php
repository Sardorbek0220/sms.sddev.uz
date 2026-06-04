<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Training extends Model
{
    protected $fillable = [
        'id',
        'operator',
        'comment',
        'training',
        'date',
        'created_at',
        'updated_at',
    ];

}
