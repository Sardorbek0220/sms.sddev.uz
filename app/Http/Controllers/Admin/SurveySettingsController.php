<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\BitrixSurveyService;
use Illuminate\Http\Request;

/**
 * Survey settings — admin UI for editing the Bitrix survey config
 * (formerly managed only from bitrix24.sddev.uz/settings.php).
 *
 * Stored in: devteam.b24_call_survey_app_settings (key='survey_ui_config').
 * Used by: BitrixSurveyService::getFormConfig() → modal in /admin/report/calls.
 */
class SurveySettingsController extends Controller
{
    public function index(BitrixSurveyService $svc)
    {
        $config = $svc->getCanonicalConfig();
        return view('admin.survey-settings', compact('config'));
    }

    public function update(Request $request, BitrixSurveyService $svc)
    {
        // Build canonical structure from form input
        $reasons = [];
        foreach ((array) $request->input('reasons', []) as $r) {
            if (!is_array($r) || empty($r['k']) || empty($r['l'])) continue;
            $reasons[] = [
                'k'                => (string) $r['k'],
                'l'                => (string) $r['l'],
                'module_set'       => in_array($r['module_set'] ?? 'common', ['common', 'payment', 'custom'], true)
                                        ? $r['module_set'] : 'common',
                'module_title'     => trim((string) ($r['module_title'] ?? 'Модули')),
                'req'              => !empty($r['req']),
                'custom_modules'   => $this->splitLines($r['custom_modules'] ?? ''),
                'custom_statuses'  => $this->splitLines($r['custom_statuses'] ?? ''),
            ];
        }

        $raw = [
            'version'          => 1,
            'common_modules'   => $this->splitLines($request->input('common_modules', '')),
            'payment_topics'   => $this->splitLines($request->input('payment_topics', '')),
            'unified_statuses' => $this->splitLines($request->input('unified_statuses', '')),
            'reasons'          => $reasons,
        ];

        try {
            $svc->saveCanonicalConfig($raw);
            return redirect()->route('admin.survey-settings')
                ->with('success', 'Настройки анкеты сохранены.');
        } catch (\Throwable $e) {
            return redirect()->route('admin.survey-settings')
                ->with('error', 'Ошибка: ' . $e->getMessage())
                ->withInput();
        }
    }

    private function splitLines($text): array
    {
        if (is_array($text)) return array_values(array_filter(array_map('trim', $text), 'strlen'));
        $lines = preg_split('/\r\n|\r|\n/', (string) $text);
        return array_values(array_filter(array_map('trim', $lines), 'strlen'));
    }
}
