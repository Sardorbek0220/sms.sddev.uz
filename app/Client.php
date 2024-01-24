<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $fillable = [
        'id',
        'name',
        'telephone',
        'company',
        'server',
        'created_at',
        'updated_at'
    ];
}
