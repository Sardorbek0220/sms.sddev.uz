<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'id',
        'operator',
        'comment',
        'client_phone',
        'audio_url',
        'request',
        'response',
        'script',
        'product',
        'date',
        'created_at',
        'updated_at',
    ];

}
