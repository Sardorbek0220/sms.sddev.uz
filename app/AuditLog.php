<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditLog extends Model
{
    public $timestamps = false;
    protected $table = 'audit_logs';

    protected $fillable = [
        'user_id', 'user_name', 'action', 'entity_type', 'entity_id',
        'method', 'path', 'ip', 'user_agent', 'message', 'payload',
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    /**
     * Record one entry. All keys optional except $action.
     */
    public static function record(string $action, array $extra = []): ?self
    {
        try {
            $user = Auth::user();
            $req  = app('request');

            return static::create(array_merge([
                'user_id'    => $user->id ?? null,
                'user_name'  => $user->name ?? null,
                'action'     => $action,
                'method'     => $req->getMethod() ?? null,
                'path'       => $req ? '/' . ltrim($req->path(), '/') : null,
                'ip'         => $req->ip() ?? null,
                'user_agent' => mb_substr((string) $req->userAgent(), 0, 250) ?: null,
                'created_at' => now(),
            ], $extra));
        } catch (\Throwable $e) {
            // Never throw from the auditor — failure to log must not break the app.
            return null;
        }
    }
}
