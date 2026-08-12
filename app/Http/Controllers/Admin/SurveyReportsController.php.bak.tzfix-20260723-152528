<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\BitrixSurveyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Survey reports — analytics for filled call surveys (b24_call_survey_logs).
 *
 * Shows admins:
 *   - aggregated stats (total, period, avg/day, with-comment%)
 *   - top-N rankings (reason, status, operator)
 *   - filterable table with CSV export
 */
class SurveyReportsController extends Controller
{
    public function index(Request $request, BitrixSurveyService $svc)
    {
        $from_date = trim((string) $request->input("from_date", "")) ?: date("Y-m-d", strtotime("-7 days"));
        $to_date   = trim((string) $request->input("to_date", "")) ?: date("Y-m-d"); if ($from_date > $to_date) { [$from_date, $to_date] = [$to_date, $from_date]; }
        $reason    = $request->input('reason_key', '');
        $status    = $request->input('status_label', '');
        $operator  = trim((string) $request->input('operator', ''));
        $department = trim((string) $request->input('department', ''));
        $phone     = trim((string) $request->input('phone', ''));
        $module    = trim((string) $request->input('module', ''));
        $hasCmt    = $request->input('has_comment', '');

        $base = DB::connection('bitrix_survey')->table('b24_call_survey_logs')
            ->whereBetween('created_at', [$from_date . ' 00:00:00', $to_date . ' 23:59:59']);

        $base->when($reason !== '', function ($q) use ($reason) { $q->where('reason_key', $reason); });
        $base->when($status !== '', function ($q) use ($status) { $q->where('status_label', $status); });
        $base->when($operator !== '', function ($q) use ($operator) { $q->where('actor_user_name', 'like', '%' . $operator . '%'); });
        // Bridge: old b24 portal (big ids 200-12500, FIO names) -> new portal (small ids 4-34, short names).
        // No stable id/name across portals — fuzzy-match by token overlap, fall back to Levenshtein.
        [$bridgeOpDeptMap, $bridgeDeptToIds] = self::buildOpDeptBridge($from_date, $to_date);
        if ($department !== '') {
            $deptLike = '%"' . str_replace('"', '', $department) . '"%';
            $opIdsForDept = $bridgeDeptToIds[$department] ?? [];
            $base->where(function ($q) use ($deptLike, $opIdsForDept) {
                $q->where('actor_department_names_json', 'like', $deptLike);
                if (!empty($opIdsForDept)) $q->orWhereIn('actor_user_id', $opIdsForDept);
            });
        }
        $base->when($phone !== '', function ($q) use ($phone) {
            $digits = preg_replace('/\D+/', '', $phone);
            $q->where(function ($qq) use ($phone, $digits) {
                $qq->where('phone_number', 'like', '%' . $phone . '%');
                if ($digits !== '') $qq->orWhere('phone_number', 'like', '%' . $digits . '%');
            });
        });
        $base->when($module !== '', function ($q) use ($module) { $q->where('modules_json', 'like', '%' . $module . '%'); });
        $base->when($hasCmt === 'yes', function ($q) { $q->whereNotNull('comment_text')->where('comment_text', '<>', ''); });
        $base->when($hasCmt === 'no', function ($q) { $q->where(function ($qq) { $qq->whereNull('comment_text')->orWhere('comment_text', ''); }); });

        // CSV export
        if ($request->input('export') === 'csv') {
            $rows = (clone $base)->orderByDesc('created_at')->limit(10000)->get();
            $filename = 'surveys_' . $from_date . '_' . $to_date . '.csv';
            return response()->stream(function () use ($rows) {
                $h = fopen('php://output', 'w');
                fwrite($h, "\xEF\xBB\xBF");
                fputcsv($h, ['ID','Дата','Телефон','Причина','Статус','Оператор','Модули','Комментарий','Call ID'], ';');
                foreach ($rows as $r) {
                    $modules = '';
                    if (!empty($r->modules_json)) {
                        $mods = json_decode($r->modules_json, true);
                        if (is_array($mods)) $modules = implode(', ', $mods);
                    }
                    fputcsv($h, [
                        $r->id,
                        $r->created_at,
                        $r->phone_number,
                        $r->reason_label,
                        $r->status_label,
                        $r->actor_user_name,
                        $modules,
                        $r->comment_text,
                        $r->call_id,
                    ], ';');
                }
                fclose($h);
            }, 200, [
                'Content-Type' => 'text/csv; charset=utf-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
        }

        // XLSX export — native, no composer dep (ZipArchive + simple XML).
        if ($request->input('export') === 'xlsx') {
            $rows = (clone $base)->orderByDesc('created_at')
                ->select('id','created_at','phone_number','reason_label','status_label',
                         'actor_user_name','actor_department_names_json','modules_json',
                         'comment_text','call_id')
                ->limit(10000)->get();

            $headers = ['ID','Дата','Телефон','Отдел','Оператор','Причина','Статус','Модули','Комментарий','Call ID'];
            $data = [];
            foreach ($rows as $r) {
                $deptArr  = !empty($r->actor_department_names_json) ? json_decode($r->actor_department_names_json, true) : null;
                $deptList = is_array($deptArr) ? array_values(array_filter(array_map('trim', $deptArr))) : [];
                $modArr   = !empty($r->modules_json) ? json_decode($r->modules_json, true) : null;
                $modList  = is_array($modArr) ? array_values(array_filter(array_map('trim', $modArr))) : [];
                $data[] = [
                    (string) $r->id,
                    (string) $r->created_at,
                    (string) ($r->phone_number ?? ''),
                    implode(', ', $deptList),
                    (string) ($r->actor_user_name ?? ''),
                    (string) ($r->reason_label ?? ''),
                    (string) ($r->status_label ?? ''),
                    implode(', ', $modList),
                    (string) ($r->comment_text ?? ''),
                    (string) ($r->call_id ?? ''),
                ];
            }

            $path = self::buildXlsx($headers, $data);
            $filename = 'anketi_' . $from_date . '_' . $to_date . '.xlsx';
            return response()->download($path, $filename, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ])->deleteFileAfterSend(true);
        }

        // Aggregates
        $total = (clone $base)->count();
        $withComment = (clone $base)->whereNotNull('comment_text')->where('comment_text', '<>', '')->count();
        $days = max(1, (strtotime($to_date) - strtotime($from_date)) / 86400 + 1);
        $avgPerDay = round($total / $days, 1);

        $topReasons = (clone $base)
            ->selectRaw('reason_key, reason_label, COUNT(*) AS c')
            ->groupBy('reason_key', 'reason_label')
            ->orderByDesc('c')->limit(8)->get();

        $topStatuses = (clone $base)
            ->selectRaw('status_label, COUNT(*) AS c')
            ->whereNotNull('status_label')->where('status_label', '<>', '')
            ->groupBy('status_label')
            ->orderByDesc('c')->limit(8)->get();

        $topOperators = (clone $base)
            ->selectRaw('actor_user_name, COUNT(*) AS c')
            ->whereNotNull('actor_user_name')->where('actor_user_name', '<>', '')
            ->groupBy('actor_user_name')
            ->orderByDesc('c')->limit(8)->get();

        $items = (clone $base)->orderByDesc('created_at')->paginate(50)->appends($request->query());

        // Dropdown options
        $reasonOptions = $this->distinctOptions('reason_key', 'reason_label', $from_date, $to_date);
        $statusOptions = (clone $base)->select('status_label')->whereNotNull('status_label')->where('status_label','<>','')
            ->groupBy('status_label')->orderBy('status_label')->pluck('status_label');

        $operatorOptions = DB::connection('bitrix_survey')->table('b24_call_survey_logs')
            ->whereBetween('created_at', [$from_date . ' 00:00:00', $to_date . ' 23:59:59'])
            ->whereNotNull('actor_user_name')->where('actor_user_name', '<>', '')
            ->groupBy('actor_user_name')->orderBy('actor_user_name')
            ->pluck('actor_user_name');


        // Use the same bridge for view fallback + dropdown options.
        $opDeptMap = $bridgeOpDeptMap;
        $deptSet = [];
        foreach ($bridgeDeptToIds as $d => $_) $deptSet[$d] = true;
        ksort($deptSet);
        $departmentOptions = array_keys($deptSet);

        $viewName = ($request->route() && $request->route()->getName() === 'admin.anketi') ? 'admin.anketi' : 'admin.survey-reports';
        return response()->view($viewName, compact(
            'items', 'from_date', 'to_date',
            'reason', 'status', 'operator', 'department', 'phone', 'module', 'hasCmt',
            'total', 'withComment', 'avgPerDay', 'days',
            'topReasons', 'topStatuses', 'topOperators',
            'reasonOptions', 'statusOptions', 'operatorOptions', 'departmentOptions',
            'opDeptMap'
        ))->withHeaders(['Cache-Control' => 'no-store, no-cache, private, must-revalidate, max-age=0', 'CDN-Cache-Control' => 'no-store', 'Cloudflare-CDN-Cache-Control' => 'no-store', 'Pragma' => 'no-cache', 'Expires' => '0']);
    }


    /** Name normalization: lowercase, strip punctuation, keep words >=4 chars as 4-char prefix tokens. */
    private static function nameTokens(string $name): array
    {
        $n = mb_strtolower($name, 'UTF-8');
        $n = preg_replace('/[^\\p{L}]+/u', ' ', $n);
        $parts = preg_split('/\\s+/u', trim($n));
        $tokens = [];
        foreach ($parts as $p) {
            if (mb_strlen($p) >= 4) $tokens[mb_substr($p, 0, 4)] = true;
        }
        return array_keys($tokens);
    }

    /**
     * Build a fuzzy bridge between b24 portals.
     * Returns [$opDeptMap, $deptToIds]:
     *   $opDeptMap[current_actor_user_id] = [dept names]   (for view fallback)
     *   $deptToIds[dept name]              = [current_actor_user_id, ...] (for filter)
     */
    private static function buildOpDeptBridge(string $from_date, string $to_date): array
    {
        // Historical: actor_user_id -> name + latest non-empty dept JSON.
        $hist = DB::connection('bitrix_survey')->select(
            "SELECT t.actor_user_id, t.actor_user_name, t.actor_department_names_json
             FROM b24_call_survey_logs t
             INNER JOIN (
                 SELECT actor_user_id, MAX(created_at) AS max_at
                 FROM b24_call_survey_logs
                 WHERE actor_user_id IS NOT NULL
                   AND actor_department_names_json IS NOT NULL
                   AND actor_department_names_json <> ''
                   AND actor_department_names_json <> '[]'
                 GROUP BY actor_user_id
             ) m ON m.actor_user_id = t.actor_user_id AND m.max_at = t.created_at"
        );
        $histEntries = [];
        foreach ($hist as $h) {
            $arr = json_decode($h->actor_department_names_json, true);
            if (!is_array($arr)) continue;
            $depts = array_values(array_filter(array_map('trim', $arr)));
            if (empty($depts)) continue;
            $histEntries[] = [
                'tokens' => self::nameTokens((string) $h->actor_user_name),
                'depts'  => $depts,
            ];
        }

        // Current operators in the selected date window (plus a safety buffer of 14 days
        // so blade dept-fallback covers operators who didn't show up in this window).
        $bufferFrom = date('Y-m-d', strtotime($from_date . ' -14 days'));
        $cur = DB::connection('bitrix_survey')->table('b24_call_survey_logs')
            ->whereBetween('created_at', [$bufferFrom . ' 00:00:00', $to_date . ' 23:59:59'])
            ->whereNotNull('actor_user_id')
            ->select('actor_user_id', DB::raw('MAX(actor_user_name) AS actor_user_name'))
            ->groupBy('actor_user_id')->get();

        $opDeptMap = [];
        $deptToIds = [];

        foreach ($cur as $c) {
            $coTokens = self::nameTokens((string) $c->actor_user_name);
            if (empty($coTokens)) continue;

            // Prefer best token overlap (>=1). Break ties by avg Levenshtein on token pairs.
            $bestDepts = null;
            $bestOverlap = 0;
            $bestLev = PHP_FLOAT_MAX;
            foreach ($histEntries as $he) {
                $overlap = count(array_intersect($coTokens, $he['tokens']));
                if ($overlap === 0) continue;
                // Average per-token min Levenshtein for tie-breaking
                $sum = 0; $cnt = 0;
                foreach ($coTokens as $t1) {
                    $mind = 99;
                    foreach ($he['tokens'] as $t2) {
                        $d = levenshtein($t1, $t2);
                        if ($d < $mind) $mind = $d;
                    }
                    $sum += $mind; $cnt++;
                }
                $avgLev = $cnt ? $sum / $cnt : 99;
                if ($overlap > $bestOverlap || ($overlap === $bestOverlap && $avgLev < $bestLev)) {
                    $bestOverlap = $overlap;
                    $bestLev = $avgLev;
                    $bestDepts = $he['depts'];
                }
            }

            // Levenshtein-only fallback for names with no token overlap (e.g. transliteration "Saburjanov"/"Sabirdjanov")
            if (!$bestDepts) {
                foreach ($histEntries as $he) {
                    $sum = 0; $cnt = 0;
                    foreach ($coTokens as $t1) {
                        $mind = 99;
                        foreach ($he['tokens'] as $t2) {
                            $d = levenshtein($t1, $t2);
                            if ($d < $mind) $mind = $d;
                        }
                        $sum += $mind; $cnt++;
                    }
                    $avgLev = $cnt ? $sum / $cnt : 99;
                    if ($avgLev <= 1.0 && $avgLev < $bestLev) {
                        $bestLev = $avgLev;
                        $bestDepts = $he['depts'];
                    }
                }
            }

            if ($bestDepts) {
                $opDeptMap[(int) $c->actor_user_id] = $bestDepts;
                foreach ($bestDepts as $d) {
                    $deptToIds[$d][(int) $c->actor_user_id] = true;
                }
            }
        }

        // Flatten dept -> id sets
        foreach ($deptToIds as $d => $set) $deptToIds[$d] = array_keys($set);

        return [$opDeptMap, $deptToIds];
    }


    /** Build a minimal .xlsx file from headers + rows. Returns the temp file path.
     *  Numbers stay as numbers; everything else as inlineStr. Header row is bold. */
    private static function buildXlsx(array $headers, array $rows): string
    {
        $colLetter = function (int $idx): string {
            $letters = '';
            $n = $idx + 1;
            while ($n > 0) {
                $r = ($n - 1) % 26;
                $letters = chr(65 + $r) . $letters;
                $n = intdiv($n - 1, 26);
            }
            return $letters;
        };

        $esc = function ($v): string {
            return htmlspecialchars((string) $v, ENT_XML1 | ENT_QUOTES, 'UTF-8');
        };

        $xmlRows = [];
        // header row (bold = styleId 1)
        $cells = [];
        foreach ($headers as $i => $h) {
            $cells[] = '<c r="' . $colLetter($i) . '1" t="inlineStr" s="1"><is><t>' . $esc($h) . '</t></is></c>';
        }
        $xmlRows[] = '<row r="1">' . implode('', $cells) . '</row>';

        // data rows
        foreach ($rows as $ri => $row) {
            $rowNum = $ri + 2;
            $cells = [];
            foreach ($row as $ci => $val) {
                $ref = $colLetter($ci) . $rowNum;
                if ($val !== '' && is_numeric($val) && (string)(int)$val === (string)$val) {
                    $cells[] = '<c r="' . $ref . '" t="n"><v>' . $esc($val) . '</v></c>';
                } else {
                    $cells[] = '<c r="' . $ref . '" t="inlineStr"><is><t xml:space="preserve">' . $esc($val) . '</t></is></c>';
                }
            }
            $xmlRows[] = '<row r="' . $rowNum . '">' . implode('', $cells) . '</row>';
        }

        // column widths
        $cols = '<cols>';
        $widths = [6, 18, 16, 24, 24, 22, 22, 28, 60, 22];
        foreach ($widths as $i => $w) {
            $cols .= '<col min="' . ($i + 1) . '" max="' . ($i + 1) . '" width="' . $w . '" customWidth="1"/>';
        }
        $cols .= '</cols>';

        $sheet = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<sheetViews><sheetView workbookViewId="0"><pane ySplit="1" topLeftCell="A2" state="frozen"/></sheetView></sheetViews>'
            . $cols
            . '<sheetData>' . implode('', $xmlRows) . '</sheetData>'
            . '</worksheet>';

        $contentTypes = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            . '</Types>';

        $rels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . '</Relationships>';

        $workbook = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheets><sheet name="Анкеты" sheetId="1" r:id="rId1"/></sheets>'
            . '</workbook>';

        $workbookRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            . '</Relationships>';

        // styles: 0 = default, 1 = bold (used for header)
        $styles = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<fonts count="2"><font><sz val="11"/><name val="Calibri"/></font><font><b/><sz val="11"/><name val="Calibri"/></font></fonts>'
            . '<fills count="2"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill></fills>'
            . '<borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>'
            . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            . '<cellXfs count="2"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/><xf numFmtId="0" fontId="1" fillId="0" borderId="0" xfId="0" applyFont="1"/></cellXfs>'
            . '</styleSheet>';

        $tmp = tempnam(sys_get_temp_dir(), 'anketi_') . '.xlsx';
        $zip = new \ZipArchive();
        if ($zip->open($tmp, \ZipArchive::CREATE) !== true) {
            throw new \RuntimeException('Cannot create xlsx');
        }
        $zip->addFromString('[Content_Types].xml', $contentTypes);
        $zip->addFromString('_rels/.rels', $rels);
        $zip->addFromString('xl/workbook.xml', $workbook);
        $zip->addFromString('xl/_rels/workbook.xml.rels', $workbookRels);
        $zip->addFromString('xl/styles.xml', $styles);
        $zip->addFromString('xl/worksheets/sheet1.xml', $sheet);
        $zip->close();
        return $tmp;
    }

    private function distinctOptions(string $keyCol, string $labelCol, string $from, string $to): array
    {
        return DB::connection('bitrix_survey')->table('b24_call_survey_logs')
            ->selectRaw("$keyCol AS k, $labelCol AS l")
            ->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->whereNotNull($keyCol)->where($keyCol, '<>', '')
            ->groupBy('k', 'l')
            ->orderBy('l')
            ->get()->all();
    }
}
