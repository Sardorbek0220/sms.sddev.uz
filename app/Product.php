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
        'solution',
        'principle_1',
        'principle_2',
        'principle_3',
        'principle_4',
        'date',
        'created_at',
        'updated_at',
    ];

    public function request_type()
    {
        return $this->belongsTo(Request_type::class, 'request_type_id');
    }

}
