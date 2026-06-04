<?php

namespace App\Http\Controllers;

use App\Services\PbxLiveStateService;
use Illuminate\Http\Request;

class PbxEventController extends Controller
{
    public function store(Request $request, PbxLiveStateService $pbxLiveStateService)
    {
        $expectedApiKey = (string) env('PBX_EVENT_API_KEY', 'MEGepAq0sBVd9gPZHyY1A07Oj7jmVC8');
        $providedApiKey = trim((string) $request->header('X-Api-Key', ''));

        abort_unless($providedApiKey !== '' && hash_equals($expectedApiKey, $providedApiKey), 401);

        $payload = $request->json()->all();
        if (!is_array($payload)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid PBX payload.',
            ], 422);
        }

        $snapshot = $pbxLiveStateService->ingest($payload);

        return response()->json([
            'status' => 'ok',
            'version' => $snapshot['version'] ?? 0,
        ]);
    }
}
