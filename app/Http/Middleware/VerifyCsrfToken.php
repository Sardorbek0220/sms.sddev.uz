<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * Use relative paths so it works on any domain (phone.sdteam.uz, phone.sddev.uz, etc.).
     *
     * @var array
     */
    protected $except = [
        'mainProcess',
        'pbxBot',
        'feedback/store',
        'feedback/afterStore',
        'feedback_new/store',
        'feedback_new/afterStore',
    ];
}
