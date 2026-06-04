<?php

namespace App\Services;

/**
 * GatewayService — single source of truth for "gateway → company" mapping.
 *
 * Gateway is the PBX number (int) the call came in on / went out via.
 * Three primary companies:
 *   - 712075995 → Sales Doctor
 *   - 781138585 → iBox
 *   - 781136022 → iDokon
 * Everything else is treated as "Other" (rare one-off numbers).
 */
class GatewayService
{
    /**
     * Primary gateway → company info.
     * Each entry: ['name', 'short', 'color', 'sms_template'].
     *
     * @var array
     */
    private static $map = [
        712075995 => [
            'name'         => 'Sales Doctor',
            'short'        => 'SD',
            'color'        => '#2563eb',  // blue
            'sms_template' => "Sales Doctor kompaniyasi sizning {gateway} nomer orqali so'nggi murojaatingizni baholashingizni so'raydi. {url}",
        ],
        781138585 => [
            'name'         => 'iBox',
            'short'        => 'iBox',
            'color'        => '#16a34a',  // green
            'sms_template' => "Ibox kompaniyasi sizning {gateway} nomer orqali so'nggi murojaatingizni baholashingizni so'raydi. {url}",
        ],
        781136022 => [
            'name'         => 'iDokon',
            'short'        => 'iDokon',
            'color'        => '#f59e0b',  // amber
            'sms_template' => "iDokon kompaniyasi sizning {gateway} nomer orqali so'nggi murojaatingizni baholashingizni so'raydi. {url}",
        ],
    ];

    /**
     * Return full info array for a given gateway.
     * Falls back to "Other" entry for unknown gateways.
     */
    public static function info($gateway): array
    {
        $gw = (int)$gateway;
        if (isset(self::$map[$gw])) {
            return self::$map[$gw] + ['gateway' => $gw];
        }
        return [
            'gateway'      => $gw,
            'name'         => 'Other',
            'short'        => $gw ? (string)$gw : '—',
            'color'        => '#64748b',
            'sms_template' => "Murojaatingizni baholang: {url}",
        ];
    }

    public static function name($gateway): string
    {
        return self::info($gateway)['name'];
    }

    public static function short($gateway): string
    {
        return self::info($gateway)['short'];
    }

    public static function color($gateway): string
    {
        return self::info($gateway)['color'];
    }

    /**
     * @return array<int,array> List of primary gateways (for filters).
     */
    public static function all(): array
    {
        $out = [];
        foreach (self::$map as $gw => $info) {
            $out[] = $info + ['gateway' => $gw];
        }
        return $out;
    }

    /**
     * Convert gateway int to dropdown options [value => label].
     */
    public static function options(): array
    {
        $opts = [];
        foreach (self::$map as $gw => $info) {
            $opts[$gw] = $info['name'];
        }
        return $opts;
    }

    /**
     * Build the SMS body for a given gateway with placeholders filled.
     */
    public static function smsBody($gateway, $url): string
    {
        $info = self::info($gateway);
        return strtr($info['sms_template'], [
            '{gateway}' => (string)$gateway,
            '{url}'     => (string)$url,
        ]);
    }

    /**
     * Is this one of the 3 primary tracked gateways?
     */
    public static function isPrimary($gateway): bool
    {
        return isset(self::$map[(int)$gateway]);
    }
}
