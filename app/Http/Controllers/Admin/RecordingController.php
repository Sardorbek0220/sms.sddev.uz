<?php

namespace App\Http\Controllers\Admin;

use App\Call;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class RecordingController extends Controller
{
    /**
     * Stream a stored recording via nginx X-Accel-Redirect so the browser
     * can seek (HTTP Range / 206 Partial Content) — handled natively by nginx.
     * Falls back to PBX URL redirect if the local copy expired.
     */
    public function stream(Call $call)
    {
        // Operator scope check — operator can only play own calls.
        $u = Auth::user();
        if ($u && method_exists($u, 'isOperator') && $u->isOperator()) {
            if ((int) $call->operator_id !== (int) $u->operator_id) {
                abort(403, 'Доступ только к своим звонкам');
            }
        }

        if ($call->recording_local_path) {
            $absolute = storage_path('app/' . $call->recording_local_path);
            if (is_file($absolute)) {
                // X-Accel-Redirect tells nginx to serve from internal location.
                // The path after /protected-recordings/ matches `alias` in nginx.
                $rel = preg_replace('#^call-recordings/#', '', $call->recording_local_path);
                $internalPath = '/protected-recordings/' . $rel;

                // Empty-body 200 with the magic header — nginx replaces it.
                return response('', HttpResponse::HTTP_OK, [
                    'X-Accel-Redirect'    => $internalPath,
                    'Content-Type'        => 'audio/mpeg',
                    'Content-Disposition' => 'inline; filename="call-' . $call->id . '.mp3"',
                    'Cache-Control'       => 'private, max-age=600',
                    // nginx will add Accept-Ranges + Content-Length itself.
                ]);
            }
        }
        // Local copy expired or never downloaded — try PBX URL fallback.
        if ($call->pbx_audio_url) {
            // Probe with a cheap HEAD first — PBX deletes recordings after ~3 days.
            // Without this check, the operator's browser hangs ~25s on a dead URL.
            $ch = curl_init($call->pbx_audio_url);
            curl_setopt_array($ch, [
                CURLOPT_NOBODY         => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_CONNECTTIMEOUT => 3,
                CURLOPT_TIMEOUT        => 5,
                CURLOPT_SSL_VERIFYPEER => false,
            ]);
            curl_exec($ch);
            $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            if ($code === 200) {
                return redirect()->away($call->pbx_audio_url);
            }
            abort(410, 'Запись больше недоступна на ATC (срок хранения истёк).');
        }
        abort(404, 'Recording not available');
    }
}
