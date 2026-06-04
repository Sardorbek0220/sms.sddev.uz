<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Like extends Model
{
    protected $fillable = [
        'id',
        'operator',
        'comment',
        'punishment',
        'date',
        'created_at',
        'updated_at',
    ];

}
