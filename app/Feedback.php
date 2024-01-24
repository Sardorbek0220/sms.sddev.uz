<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    protected $fillable = [
        'id',
        'call_id',
        'complaint',
        'advice',
        'solved',
        'created_at',
        'updated_at'
    ];

    public function call()
    {
        return $this->belongsTo(Call::class, 'call_id');
    }
}
