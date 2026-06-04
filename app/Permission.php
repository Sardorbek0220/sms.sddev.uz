<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $primaryKey = 'permission_key';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['permission_key', 'label', 'category', 'sort_order'];
}
