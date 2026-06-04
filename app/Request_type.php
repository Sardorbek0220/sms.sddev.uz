<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Request_type extends Model
{
    protected $fillable = [
        'id',
        'name'
    ];
    public $timestamps = false;

    public function product()
    {
        return $this->hasMany(Product::class);
    }
}
