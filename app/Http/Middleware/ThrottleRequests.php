<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Routing\Middleware\ThrottleRequests as BaseThrottleRequests;

/**
 * Rate-limit middleware with an IP whitelist.
 *
 * Whitelisted IPs (internal services / cron / monitoring origin) bypass
 * the rate limiter entirely. Everyone else hits the configured limit.
 */
class ThrottleRequests extends BaseThrottleRequests
{
    /**
     * IPs that bypass throttling entirely.
     * Add real-IP candidates here (CF-Connecting-IP is what arrives behind Cloudflare).
     */
    protected const WHITELIST = [
        '204.168.199.61',   // call.sdteam.uz (production server itself — internal cron / curls)
        '127.0.0.1',        // loopback
        '::1',              // loopback IPv6
    ];

    public function handle($request, Closure $next, $maxAttempts = 60, $decayMinutes = 1, $prefix = '')
    {
        if (in_array($request->ip(), self::WHITELIST, true)) {
            return $next($request);
        }

        return parent::handle($request, $next, $maxAttempts, $decayMinutes, $prefix);
    }
}
