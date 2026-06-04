<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Unknown_client extends Model
{
    protected $fillable = [
        'id',
        'phone',
        'direction',
        'event',
        'operator',
        'created_at',
        'updated_at'
    ];

}
