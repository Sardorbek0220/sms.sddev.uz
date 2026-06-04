<?php

namespace App\Listeners;

use App\AuditLog;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Failed;

class AuditAuthSubscriber
{
    public function onLogin(Login $event): void
    {
        AuditLog::record('login', [
            'user_id'   => $event->user->id ?? null,
            'user_name' => $event->user->name ?? null,
            'message'   => 'Вход в систему',
        ]);
    }

    public function onLogout(Logout $event): void
    {
        AuditLog::record('logout', [
            'user_id'   => $event->user->id ?? null,
            'user_name' => $event->user->name ?? null,
            'message'   => 'Выход из системы',
        ]);
    }

    public function onFailed(Failed $event): void
    {
        AuditLog::record('login_failed', [
            'message' => 'Неудачная попытка входа',
            'payload' => [
                'email' => $event->credentials['email'] ?? $event->credentials['login'] ?? null,
            ],
        ]);
    }

    public function subscribe($events): void
    {
        $events->listen(Login::class,  [self::class, 'onLogin']);
        $events->listen(Logout::class, [self::class, 'onLogout']);
        $events->listen(Failed::class, [self::class, 'onFailed']);
    }
}
