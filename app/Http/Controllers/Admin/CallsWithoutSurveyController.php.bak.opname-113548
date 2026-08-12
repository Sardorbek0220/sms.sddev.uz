<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Calls that don't have a Bitrix24 survey attached — UI for spotting
 * operators who skipped filling the form. Same look as /admin/anketi.
 */
class CallsWithoutSurveyController extends Controller
{
    /** Known gateway → label map (top 3 covers ~99% of traffic). */
    public const GATEWAYS = [
        '712075995' => 'SD',
        '781138585' => 'iBox',
        '781136022' => 'iDokon',
    ];

    public function index(Request $request)
    {
        $from_date = trim((string) $request->input('from_date', '')) ?: date('Y-m-d', strtotime('-7 days'));
        $to_date   = trim((string) $request->input('to_date', ''))   ?: date('Y-m-d');
        if ($from_date > $to_date) { [$from_date, $to_date] = [$to_date, $from_date]; }

        $operator   = $request->input('operator_id', '');
        $gateway    = $request->input('gateway', '');
        $direction  = $request->input('direction', '');
        $callState  = $request->input('call_state', '');
        $phone      = trim((string) $request->input('phone', ''));
        $minDialog  = (int) $request->input('min_dialog', 0);

        $fromTs = $from_date . ' 00:00:00';
        $toTs   = $to_date   . ' 23:59:59';

        // Surveyed call_ids — extend look-ahead 30 days to catch surveys filled late.
        $surveyedIds = $this->loadSurveyedCallIds($from_date, $to_date);

        $isOperator = \Illuminate\Support\Facades\Auth::check()
            && method_exists(\Illuminate\Support\Facades\Auth::user(), 'isOperator')
            && \Illuminate\Support\Facades\Auth::user()->isOperator();

        $base = DB::table('calls')
            ->whereBetween('created_at', [$fromTs, $toTs])
            ->whereNotIn('id', $surveyedIds ?: [0])
            // hide missed calls — this page is only about real conversations
            // that the operator should have filled the anketa for
            ->where('event', '!=', 'call_missed')
            ->where('dialog_duration', '>', 0)
            // operator scope: only own calls
            ->when($isOperator, function ($q) {
                $q->where('operator_id', \Illuminate\Support\Facades\Auth::user()->operator_id);
            });

        $base->when($operator !== '',  function ($q) use ($operator)  { $q->where('operator_id', (int) $operator); });
        $base->when($gateway !== '',   function ($q) use ($gateway)   { $q->where('gateway', $gateway); });
        $base->when($direction !== '', function ($q) use ($direction) { $q->where('direction', $direction); });
        $base->when($callState !== '', function ($q) use ($callState) { $q->where('event', $callState); });
        $base->when($phone !== '',     function ($q) use ($phone) {
            $digits = preg_replace('/\D+/', '', $phone);
            $q->where(function ($qq) use ($phone, $digits) {
                $qq->where('client_telephone', 'like', '%' . $phone . '%');
                if ($digits !== '') $qq->orWhere('client_telephone', 'like', '%' . $digits . '%');
            });
        });
        $base->when($minDialog > 0, function ($q) use ($minDialog) { $q->where('dialog_duration', '>=', $minDialog); });

        // Aggregates (counts)
        $total          = (clone $base)->count();
        $withDialog     = (clone $base)->where('dialog_duration', '>', 0)->count();
        $missed         = (clone $base)->where('event', 'call_missed')->count();
        $totalInPeriod  = DB::table('calls')->whereBetween('created_at', [$fromTs, $toTs])->count();
        $surveyedCount  = $totalInPeriod - (clone $base)->reorder()->count();
        // recompute: surveyed in this period = total calls in period - calls-without-survey-with-NO-filters
        $unfilteredNoSurvey = DB::table('calls')->whereBetween('created_at', [$fromTs, $toTs])
            ->whereNotIn('id', $surveyedIds ?: [0])->count();
        $coveragePct = $totalInPeriod > 0 ? round(($totalInPeriod - $unfilteredNoSurvey) / $totalInPeriod * 100, 1) : 0;

        // Per-operator breakdown of MISSING surveys (top, all filters applied)
        $byOperator = (clone $base)
            ->selectRaw('operator_id, COUNT(*) c, SUM(CASE WHEN dialog_duration > 0 THEN 1 ELSE 0 END) c_dialog')
            ->whereNotNull('operator_id')
            ->groupBy('operator_id')
            ->orderByDesc('c')->limit(20)->get();

        // Resolve operator names
        $opNames = DB::table('operators')->pluck('name', 'id');

        // Dropdown options
        $operatorOptions = DB::table('operators')
            ->where('active', 'Y')->orderBy('name')
            ->pluck('name', 'id');

        // Paginate (50 per page)
        $items = $base->orderByDesc('created_at')
            ->paginate(50)->appends($request->query());

        $days = max(1, (strtotime($to_date) - strtotime($from_date)) / 86400 + 1);

        return response()->view('admin.calls-no-anketa', compact(
            'items', 'from_date', 'to_date',
            'operator', 'gateway', 'direction', 'callState', 'phone', 'minDialog',
            'total', 'withDialog', 'missed', 'totalInPeriod', 'coveragePct', 'days',
            'byOperator', 'opNames', 'operatorOptions', 'isOperator'
        ))->withHeaders([
            'Cache-Control' => 'no-store, no-cache, private, must-revalidate, max-age=0',
            'CDN-Cache-Control' => 'no-store',
            'Cloudflare-CDN-Cache-Control' => 'no-store',
        ]);
    }

    /** Returns array<int> of local call IDs that ALREADY have a survey. Look-ahead 30 days
     *  beyond $to_date so late-filled surveys count too. */
    private function loadSurveyedCallIds(string $from_date, string $to_date): array
    {
        $lookahead = Carbon::parse($to_date)->addDays(30)->endOfDay();
        $rawIds = DB::connection('bitrix_survey')->table('b24_call_survey_logs')
            ->where('created_at', '>=', $from_date . ' 00:00:00')
            ->where('created_at', '<=', $lookahead)
            ->where('call_id', 'like', '%:call:%')
            ->pluck('call_id');

        $ids = [];
        foreach ($rawIds as $cid) {
            if (preg_match('|:call:(\d+):|', (string) $cid, $m)) {
                $ids[(int) $m[1]] = true;
            }
        }
        return array_keys($ids);
    }

    /** Sidebar badge — today's calls without a Bitrix survey.
     *
     *  Mirrors ReportController::monitoringSurveysCount EXACTLY (today range,
     *  phone normalized + ±BITRIX_SURVEY_MATCH_HOURS window) so the badge equals
     *  the "без анкеты" complement of survey.today shown on /admin/monitoring,
     *  i.e. total_calls(today) − with_survey(today). */
    public static function pendingCount(): int
    {
        $from = date('Y-m-d') . ' 00:00:00';
        $to   = date('Y-m-d') . ' 23:59:59';

        $totalCalls = (int) DB::table('calls')
            ->whereBetween('created_at', [$from, $to])
            ->count();
        if ($totalCalls === 0) return 0;

        $matchHours = (int) env('BITRIX_SURVEY_MATCH_HOURS', 72);
        $b24Table = env('BITRIX_SURVEY_DB_TABLE', 'b24_call_survey_logs');
        if (!preg_match('/^[A-Za-z0-9_]+$/', $b24Table)) $b24Table = 'b24_call_survey_logs';

        // Index today's calls by normalized phone.
        $calls = DB::table('calls')
            ->whereBetween('created_at', [$from, $to])
            ->select('id', 'client_telephone', 'created_at')
            ->get();

        $phoneIndex = []; // normalized_phone => [['id'=>int,'ts'=>int], ...]
        foreach ($calls as $c) {
            $norm = \App\Support\PhoneNumber::canonicalUzDigits($c->client_telephone);
            if ($norm === null) continue;
            $phoneIndex[$norm][] = ['id' => (int) $c->id, 'ts' => strtotime($c->created_at)];
        }

        // Match against Bitrix surveys in the extended ±matchHours window.
        $extFrom = Carbon::parse($from)->subHours($matchHours)->format('Y-m-d H:i:s');
        $extTo   = Carbon::parse($to)->addHours($matchHours)->format('Y-m-d H:i:s');
        $windowSec = $matchHours * 3600;
        $matchedCallIds = [];

        try {
            $b24 = DB::connection('bitrix_survey')
                ->table($b24Table)
                ->whereBetween('created_at', [$extFrom, $extTo])
                ->select('phone_number', 'created_at')
                ->get();

            foreach ($b24 as $b) {
                $norm = \App\Support\PhoneNumber::canonicalUzDigits($b->phone_number);
                if ($norm === null || empty($phoneIndex[$norm])) continue;
                $bts = strtotime($b->created_at);
                foreach ($phoneIndex[$norm] as $cc) {
                    if (abs($bts - $cc['ts']) <= $windowSec) {
                        $matchedCallIds[$cc['id']] = true;
                    }
                }
            }
        } catch (\Throwable $e) {
            // Bitrix DB unavailable — treat all calls as un-surveyed (same
            // fallback behaviour as monitoringSurveysCount).
        }

        return max(0, $totalCalls - count($matchedCallIds));
    }
}
