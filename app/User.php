<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'role', 'operator_id', 'live_survey_widget_enabled',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'operator_id' => 'integer', 'live_survey_widget_enabled' => 'boolean',
    ];

    public function operator()
    {
        return $this->belongsTo(Operator::class, 'operator_id');
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isOperator(): bool
    {
        return $this->role === 'operator';
    }

    /**
     * True if user has the given permission key.
     * Admins always pass. Other roles must have an explicit grant in user_permissions.
     */
    public function hasPermission(string $key): bool
    {
        if ($this->isAdmin()) return true;
        if ($this->isOperator()) return false;
        // cache per request
        if (!isset($this->_grantsCache)) {
            $this->_grantsCache = \Illuminate\Support\Facades\DB::table('user_permissions')
                ->where('user_id', $this->id)->pluck('permission_key')->all();
        }
        return in_array($key, $this->_grantsCache, true);
    }

    public $_grantsCache;
}
