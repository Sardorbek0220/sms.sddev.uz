<?php

namespace App\Http\Middleware;

use App\AuditLog;
use Closure;
use Illuminate\Http\Request;

/**
 * Records every admin write request (POST/PUT/PATCH/DELETE) into audit_logs.
 * GET-requests are NOT logged to keep the table small (browsing the dashboard
 * shouldn't generate hundreds of rows). Logins/logouts are caught separately
 * via auth events.
 */
class LogAdminActivity
{
    private const SENSITIVE_KEYS = [
        'password', 'password_confirmation', 'old_password', 'new_password',
        'token', 'access_token', 'refresh_token', '_token', 'api_key',
    ];

    private const MAX_PAYLOAD_BYTES = 4096;

    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Only log writes — GETs would flood the table.
        if (!in_array($request->getMethod(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return $response;
        }

        $payload = $this->sanitizePayload($request->all());
        $action = strtolower($request->getMethod());

        AuditLog::record('http_' . $action, [
            'message' => $this->describeRoute($request, $response),
            'payload' => $payload,
        ]);

        return $response;
    }

    private function sanitizePayload(array $input): array
    {
        $clean = [];
        foreach ($input as $k => $v) {
            if (in_array(strtolower((string) $k), self::SENSITIVE_KEYS, true)) {
                $clean[$k] = '***';
                continue;
            }
            if (is_array($v)) {
                $clean[$k] = $this->sanitizePayload($v);
            } elseif (is_object($v)) {
                $clean[$k] = '[object ' . get_class($v) . ']';
            } else {
                $clean[$k] = is_string($v) ? mb_substr($v, 0, 500) : $v;
            }
        }
        // Hard size cap.
        $encoded = json_encode($clean, JSON_UNESCAPED_UNICODE);
        if ($encoded !== false && strlen($encoded) > self::MAX_PAYLOAD_BYTES) {
            return ['_truncated' => true, '_size' => strlen($encoded)];
        }
        return $clean;
    }

    private function describeRoute(Request $request, $response): string
    {
        $path = '/' . ltrim($request->path(), '/');
        $method = $request->getMethod();
        $status = method_exists($response, 'getStatusCode') ? $response->getStatusCode() : '?';
        return "{$method} {$path} → {$status}";
    }
}
