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

    /** Sidebar badge — count of unanswered surveys in the last 7 days. */
    public static function pendingCount(): int
    {
        $from = date('Y-m-d', strtotime('-7 days')) . ' 00:00:00';
        $to   = date('Y-m-d') . ' 23:59:59';
        $surveyedIds = (new self)->loadSurveyedCallIds(substr($from, 0, 10), substr($to, 0, 10));
        return DB::table('calls')->whereBetween('created_at', [$from, $to])
            ->whereNotIn('id', $surveyedIds ?: [0])
            ->where('dialog_duration', '>', 0)  // only those with actual conversation
            ->count();
    }
}
