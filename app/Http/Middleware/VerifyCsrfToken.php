<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        'https://sms.sddev.uz/mainProcess',
        'https://sms.sddev.uz/pbxBot',
        'https://sms.sddev.uz/feedback/store',
        'https://sms.sddev.uz/feedback/afterStore',
        'https://sms.salesdoc.uz/mainProcess',
        'https://sms.salesdoc.uz/pbxBot',
        'https://sms.salesdoc.uz/feedback/store',
        'https://sms.salesdoc.uz/feedback/afterStore',
        'https://sms.salesdoc.uz/feedback_new/store',
        'https://sms.salesdoc.uz/feedback_new/afterStore'
    ];
}
