<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * Replaces "Operator_name" placeholder rows with something useful in REPORTS:
 *
 *   1. Last real operator who talked to the same client (most informative).
 *   2. Fallback: "Ext. {phone}" — the SIP extension number itself.
 *
 * Pure display layer — does NOT modify the database.
 */
class OperatorDisplayService
{
    public const PLACEHOLDER_NAME = 'Operator_name';

    /**
     * Attach `operator_display_name` to each call object.
     *
     * @param iterable $calls  Collection|array of call rows. Each must have:
     *                          - operator (Operator|object with ->name, ->phone)
     *                          - client_telephone
     *                          - id, created_at
     */
    public static function attach(iterable $calls): void
    {
        // Phase 1: collect customer phones whose calls have a placeholder operator.
        $clientsToResolve = [];
        foreach ($calls as $c) {
            if (!isset($c->operator) || $c->operator === null) {
                $c->operator_display_name = '—';
                continue;
            }
            if (self::isPlaceholder($c->operator->name ?? '')) {
                $clientsToResolve[$c->client_telephone ?? ''] = true;
                $c->operator_display_name = null; // resolved below
            } else {
                $c->operator_display_name = $c->operator->name ?: '—';
            }
        }
        if (empty($clientsToResolve)) return;

        // Phase 2: one query — last REAL operator name per client phone.
        $clientPhones = array_values(array_filter(
            array_keys($clientsToResolve),
            function ($p) { return $p !== ''; }
        ));

        $lastReal = [];
        if (!empty($clientPhones)) {
            $rows = DB::table('calls AS c')
                ->join('operators AS o', 'o.id', '=', 'c.operator_id')
                ->whereIn('c.client_telephone', $clientPhones)
                ->where('o.name', '<>', self::PLACEHOLDER_NAME)
                ->where('c.dialog_duration', '>', 0)   // only ANSWERED calls = real engagement
                ->orderByDesc('c.created_at')
                ->select('c.client_telephone', 'o.name')
                ->get();
            foreach ($rows as $r) {
                if (!isset($lastReal[$r->client_telephone])) {
                    $lastReal[$r->client_telephone] = $r->name;
                }
            }
        }

        // Phase 3: write back to each call.
        foreach ($calls as $c) {
            if ($c->operator_display_name !== null) continue;
            $phone = $c->client_telephone ?? '';
            if (isset($lastReal[$phone])) {
                $c->operator_display_name = $lastReal[$phone];
                $c->operator_display_hint = 'из истории клиента';
            } else {
                $ext = (string) ($c->operator->phone ?? '');
                $c->operator_display_name = $ext !== '' ? 'Ext. ' . $ext : '—';
                $c->operator_display_hint = 'без истории';
            }
        }
    }

    /**
     * Resolve a single placeholder operator (used outside of list contexts).
     */
    public static function resolveOne(?string $operatorName, ?string $extPhone, ?string $clientPhone = null): string
    {
        if (!self::isPlaceholder($operatorName)) {
            return $operatorName ?: '—';
        }
        if ($clientPhone) {
            $last = DB::table('calls AS c')
                ->join('operators AS o', 'o.id', '=', 'c.operator_id')
                ->where('c.client_telephone', $clientPhone)
                ->where('o.name', '<>', self::PLACEHOLDER_NAME)
                ->where('c.dialog_duration', '>', 0)
                ->orderByDesc('c.created_at')
                ->value('o.name');
            if ($last) return $last;
        }
        return $extPhone !== null && $extPhone !== '' ? 'Ext. ' . $extPhone : '—';
    }

    public static function isPlaceholder(?string $name): bool
    {
        return trim($name ?? '') === self::PLACEHOLDER_NAME;
    }
}
