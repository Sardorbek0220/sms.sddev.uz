<?php

namespace App\Services;

use App\Feedback;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * FeedbackReportService — read-only aggregations on `feedback` table.
 *
 * Feedback is yes/no answers to 4 questions:
 *    q1..q4 ∈ { -1: "Yo'q" (No), 0: skip, 1: "Ha" (Yes) }
 *
 * "Score" = number of "Yes" answers (0..4) — used as the satisfaction proxy.
 */
class FeedbackReportService
{
    /**
     * Aggregated stats for a date range (defaults to today).
     * Returns [
     *   'total','solved','unsolved','with_complaint',
     *   'avg_score', 'pct_q1_yes','pct_q2_yes','pct_q3_yes','pct_q4_yes',
     *   'count_q1_yes','count_q1_no', ... q4 ... ,
     * ]
     */
    public function summary($from = null, $to = null, $gateway = null): array
    {
        list($from, $to) = $this->range($from, $to);

        $q = $this->baseQuery($from, $to, $gateway);

        $row = (clone $q)->selectRaw(
            'COUNT(*) AS total,
             SUM(CASE WHEN feedback.solved=1 THEN 1 ELSE 0 END) AS solved,
             SUM(CASE WHEN feedback.complaint IS NOT NULL AND feedback.complaint <> "" THEN 1 ELSE 0 END) AS with_complaint,
             SUM(CASE WHEN feedback.q1=1 THEN 1 ELSE 0 END) AS q1_yes,
             SUM(CASE WHEN feedback.q1=-1 THEN 1 ELSE 0 END) AS q1_no,
             SUM(CASE WHEN feedback.q2=1 THEN 1 ELSE 0 END) AS q2_yes,
             SUM(CASE WHEN feedback.q2=-1 THEN 1 ELSE 0 END) AS q2_no,
             SUM(CASE WHEN feedback.q3=1 THEN 1 ELSE 0 END) AS q3_yes,
             SUM(CASE WHEN feedback.q3=-1 THEN 1 ELSE 0 END) AS q3_no,
             SUM(CASE WHEN feedback.q4=1 THEN 1 ELSE 0 END) AS q4_yes,
             SUM(CASE WHEN feedback.q4=-1 THEN 1 ELSE 0 END) AS q4_no'
        )->first();

        $total = $row ? (int)$row->total : 0;
        $pct = function ($yes) use ($total) {
            return $total > 0 ? round((int)$yes / $total * 100, 1) : 0.0;
        };
        $sumYes = $row ? ((int)$row->q1_yes + (int)$row->q2_yes + (int)$row->q3_yes + (int)$row->q4_yes) : 0;
        $avgScore = $total > 0 ? round($sumYes / $total, 2) : 0.0;

        return [
            'total'           => $total,
            'solved'          => $row ? (int)$row->solved : 0,
            'unsolved'        => $row ? $total - (int)$row->solved : 0,
            'with_complaint'  => $row ? (int)$row->with_complaint : 0,
            'avg_score'       => $avgScore,            // 0..4
            'count_q1_yes'    => $row ? (int)$row->q1_yes : 0,
            'count_q1_no'     => $row ? (int)$row->q1_no  : 0,
            'count_q2_yes'    => $row ? (int)$row->q2_yes : 0,
            'count_q2_no'     => $row ? (int)$row->q2_no  : 0,
            'count_q3_yes'    => $row ? (int)$row->q3_yes : 0,
            'count_q3_no'     => $row ? (int)$row->q3_no  : 0,
            'count_q4_yes'    => $row ? (int)$row->q4_yes : 0,
            'count_q4_no'     => $row ? (int)$row->q4_no  : 0,
            'pct_q1_yes'      => $pct($row ? $row->q1_yes : 0),
            'pct_q2_yes'      => $pct($row ? $row->q2_yes : 0),
            'pct_q3_yes'      => $pct($row ? $row->q3_yes : 0),
            'pct_q4_yes'      => $pct($row ? $row->q4_yes : 0),
        ];
    }

    /**
     * Recent feedbacks with complaints (negative comments).
     * Returns array of stdClass: [id, call_id, complaint, created_at, gateway, client_telephone].
     */
    public function recentComplaints($limit = 10, $from = null, $to = null, $gateway = null): array
    {
        list($from, $to) = $this->range($from, $to);
        $rows = $this->baseQuery($from, $to, $gateway)
            ->whereNotNull('feedback.complaint')
            ->where('feedback.complaint', '<>', '')
            ->select(
                'feedback.id',
                'feedback.call_id',
                'feedback.complaint',
                'feedback.created_at',
                'feedback.q1', 'feedback.q2', 'feedback.q3', 'feedback.q4',
                'calls.gateway',
                'calls.client_telephone',
                'calls.operator_id'
            )
            ->orderByDesc('feedback.created_at')
            ->limit($limit)
            ->get();

        return $rows->all();
    }

    /**
     * Score distribution: how many feedbacks scored 0/1/2/3/4 yes-answers.
     * Returns [0=>count, 1=>count, ..., 4=>count].
     */
    public function scoreDistribution($from = null, $to = null, $gateway = null): array
    {
        list($from, $to) = $this->range($from, $to);

        $rows = $this->baseQuery($from, $to, $gateway)
            ->selectRaw(
                '(CASE WHEN feedback.q1=1 THEN 1 ELSE 0 END
                + CASE WHEN feedback.q2=1 THEN 1 ELSE 0 END
                + CASE WHEN feedback.q3=1 THEN 1 ELSE 0 END
                + CASE WHEN feedback.q4=1 THEN 1 ELSE 0 END) AS yes_count,
                COUNT(*) AS c'
            )
            ->groupBy('yes_count')
            ->pluck('c', 'yes_count');

        $out = [];
        for ($i = 0; $i <= 4; $i++) {
            $out[$i] = (int)($rows[$i] ?? 0);
        }
        return $out;
    }

    /** ---- internal ---- */
    private function baseQuery($from, $to, $gateway)
    {
        $q = DB::table('feedback')
            ->join('calls', 'calls.id', '=', 'feedback.call_id')
            ->whereBetween('feedback.created_at', [$from, $to]);
        if ($gateway) $q->where('calls.gateway', (int)$gateway);
        return $q;
    }

    private function range($from, $to): array
    {
        $start = $from ? Carbon::parse($from)->startOfDay() : Carbon::today()->startOfDay();
        $end   = $to   ? Carbon::parse($to)->endOfDay()     : Carbon::today()->endOfDay();
        return [$start->toDateTimeString(), $end->toDateTimeString()];
    }
}
