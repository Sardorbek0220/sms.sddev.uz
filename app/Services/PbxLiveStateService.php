<?php

namespace App\Services;

use App\Operator_time;

class PbxLiveStateService
{
    private const BRIDGE_STALE_AFTER_SECONDS = 30;
    private const STATE_RETENTION_SECONDS = 86400;

    public function ingest(array $payload): array
    {
        $snapshot = $this->loadSnapshot();
        $event = trim((string) ($payload['event'] ?? ''));
        $data = is_array($payload['data'] ?? null) ? $payload['data'] : [];
        $nowUnix = time();
        $nowIso = date('Y-m-d H:i:s', $nowUnix);

        $snapshot['bridge_seen_at'] = $nowIso;
        $snapshot['bridge_seen_at_unix'] = $nowUnix;

        if ($event === 'bridge_heartbeat' || $event === 'bridge_connected') {
            $this->saveSnapshot($snapshot);

            return $this->buildResponseSnapshot($snapshot);
        }

        $uid = trim((string) ($data['uid'] ?? ''));
        if ($event !== '' && $uid !== '') {
            $operatorState = $this->buildOperatorState(
                $snapshot['operators'][$uid] ?? [],
                $event,
                $data,
                $nowIso,
                $nowUnix
            );

            $snapshot['operators'][$uid] = $operatorState;
            $snapshot['version'] = (int) ($snapshot['version'] ?? 0) + 1;
            $snapshot['last_event'] = [
                'event' => $event,
                'uid' => $uid,
                'status' => $operatorState['status'],
                'received_at' => $nowIso,
            ];
            $snapshot['last_event_at'] = $nowIso;
            $snapshot['last_event_at_unix'] = $nowUnix;

            if ($event === 'user_registration') {
                $this->syncOperatorTime($data);
            }
        }

        $this->pruneOldStates($snapshot, $nowUnix);
        $this->saveSnapshot($snapshot);

        return $this->buildResponseSnapshot($snapshot);
    }

    public function snapshot(): array
    {
        $snapshot = $this->loadSnapshot();
        $this->pruneOldStates($snapshot, time());

        return $this->buildResponseSnapshot($snapshot);
    }

    private function buildOperatorState(array $existing, string $event, array $data, string $nowIso, int $nowUnix): array
    {
        $status = $existing['status'] ?? 'unregistered';

        if ($event === 'user_registration') {
            $status = $this->normalizeStatus($data['state'] ?? null) ?: $status;
        } elseif ($event === 'user_blf') {
            $status = $this->normalizeStatus($data['status'] ?? null) ?: $status;
        } elseif ($event === 'user_status') {
            $status = $this->normalizeStatus($data['status'] ?? null) ?: $status;
        }

        return [
            'uid' => trim((string) ($data['uid'] ?? ($existing['uid'] ?? ''))),
            'status' => $status,
            'registration_state' => $this->normalizeStatus($data['state'] ?? ($existing['registration_state'] ?? '')),
            'blf_status' => $this->normalizeStatus($data['status'] ?? ($existing['blf_status'] ?? '')),
            'source_event' => $event,
            'ip' => trim((string) ($data['ip'] ?? ($existing['ip'] ?? ''))),
            'port' => trim((string) ($data['port'] ?? ($existing['port'] ?? ''))),
            'pbx_date' => isset($data['date']) ? (int) $data['date'] : ($existing['pbx_date'] ?? null),
            'updated_at' => $nowIso,
            'updated_at_unix' => $nowUnix,
            'raw' => [
                'status' => $data['status'] ?? null,
                'state' => $data['state'] ?? null,
                'caller_id_number' => $data['caller_id_number'] ?? null,
                'destination_number' => $data['destination_number'] ?? null,
                'direction' => $data['direction'] ?? null,
            ],
        ];
    }

    private function buildResponseSnapshot(array $snapshot): array
    {
        $bridgeSeenAtUnix = (int) ($snapshot['bridge_seen_at_unix'] ?? 0);

        return [
            'version' => (int) ($snapshot['version'] ?? 0),
            'bridge_connected' => $bridgeSeenAtUnix > 0 && $bridgeSeenAtUnix >= (time() - self::BRIDGE_STALE_AFTER_SECONDS),
            'bridge_seen_at' => $snapshot['bridge_seen_at'] ?? null,
            'last_event_at' => $snapshot['last_event_at'] ?? null,
            'last_event' => $snapshot['last_event'] ?? null,
            'operators' => $snapshot['operators'] ?? [],
            'fallback_registered_uids' => $this->registeredOperatorUids(),
        ];
    }

    private function registeredOperatorUids(): array
    {
        try {
            return Operator_time::query()
                ->where('unregister', 0)
                ->where('created_at', '>=', date('Y-m-d 00:00:00'))
                ->pluck('uid')
                ->map(function ($uid) {
                    return trim((string) $uid);
                })
                ->filter()
                ->unique()
                ->values()
                ->all();
        } catch (\Throwable $exception) {
            return [];
        }
    }

    private function syncOperatorTime(array $data): void
    {
        $state = $this->normalizeStatus($data['state'] ?? null);
        $uid = trim((string) ($data['uid'] ?? ''));
        $port = trim((string) ($data['port'] ?? ''));
        $ip = trim((string) ($data['ip'] ?? ''));
        $pbxDate = isset($data['date']) ? (int) $data['date'] : time();

        if ($uid === '' || $port === '' || $ip === '') {
            return;
        }

        try {
            if ($state === 'register' || $state === 'registered') {
                $existing = Operator_time::query()
                    ->where('uid', $uid)
                    ->where('port', $port)
                    ->where('ip', $ip)
                    ->where('unregister', 0)
                    ->where('created_at', '>', date('Y-m-d'))
                    ->first();

                if ($existing === null) {
                    $operatorTime = Operator_time::create([
                        'uid' => $uid,
                        'register' => 1,
                        'unregister' => 0,
                        'ip' => $ip,
                        'port' => $port,
                        'timestamp_reg' => $pbxDate,
                    ]);

                    $operatorTime->save();
                }

                return;
            }

            if ($state === 'unregister' || $state === 'unregistered') {
                $operatorTime = Operator_time::query()
                    ->where('uid', $uid)
                    ->where('port', $port)
                    ->where('ip', $ip)
                    ->orderBy('timestamp_reg', 'desc')
                    ->first();

                if ($operatorTime !== null) {
                    $operatorTime->unregister = 1;
                    $operatorTime->timestamp_unreg = $pbxDate;
                    $operatorTime->save();
                }
            }
        } catch (\Throwable $exception) {
            // Keep live-state ingestion resilient even if Operator_time sync fails.
        }
    }

    private function pruneOldStates(array &$snapshot, int $nowUnix): void
    {
        if (empty($snapshot['operators']) || !is_array($snapshot['operators'])) {
            $snapshot['operators'] = [];

            return;
        }

        foreach ($snapshot['operators'] as $uid => $state) {
            $updatedAtUnix = (int) ($state['updated_at_unix'] ?? 0);
            if ($updatedAtUnix > 0 && $updatedAtUnix < ($nowUnix - self::STATE_RETENTION_SECONDS)) {
                unset($snapshot['operators'][$uid]);
            }
        }
    }

    private function normalizeStatus($value): string
    {
        return strtolower(trim((string) $value));
    }

    private function loadSnapshot(): array
    {
        $path = $this->snapshotPath();
        if (!is_file($path)) {
            return $this->defaultSnapshot();
        }

        $decoded = json_decode((string) file_get_contents($path), true);
        if (!is_array($decoded)) {
            return $this->defaultSnapshot();
        }

        $decoded['operators'] = is_array($decoded['operators'] ?? null) ? $decoded['operators'] : [];

        return array_merge($this->defaultSnapshot(), $decoded);
    }

    private function saveSnapshot(array $snapshot): void
    {
        $path = $this->snapshotPath();
        $directory = dirname($path);
        if (!is_dir($directory)) {
            @mkdir($directory, 0775, true);
        }

        file_put_contents($path, json_encode($snapshot, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), LOCK_EX);
    }

    private function snapshotPath(): string
    {
        return storage_path('app/pbx-live-state.json');
    }

    private function defaultSnapshot(): array
    {
        return [
            'version' => 0,
            'bridge_seen_at' => null,
            'bridge_seen_at_unix' => 0,
            'last_event_at' => null,
            'last_event_at_unix' => 0,
            'last_event' => null,
            'operators' => [],
        ];
    }
}
