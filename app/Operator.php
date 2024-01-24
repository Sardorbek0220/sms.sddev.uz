<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Operator extends Model
{
    protected $fillable = [
        'id',
        'name',
        'phone',
        'active',
        'created_at',
        'updated_at'
    ];

    public function call()
    {
        return $this->hasMany(Call::class);
    }

}
