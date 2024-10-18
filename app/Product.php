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
        'request_type_id',
        'request',
        'response',
        'script',
        'product',
        'date',
        'created_at',
        'updated_at',
    ];

    public function request_type()
    {
        return $this->belongsTo(Request_type::class, 'request_type_id');
    }

}
