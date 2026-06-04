@extends('admin.layouts.index')

@section('content')
<div class="content-wrapper p-3 p-md-4">

    <x-page-header title="Все отзывы"
                   subtitle="Период: {{ $from_date }} — {{ $to_date }} · {{ $summary['total'] }} анкет"
                   :breadcrumbs="[
                       ['label' => 'Главная', 'url' => route('admin.bigreport')],
                       ['label' => 'Отзывы клиентов'],
                   ]">
        <x-slot name="actions">
            <a href="{{ route('feedback.all') }}?{{ http_build_query(array_merge(request()->query(), ['export'=>'csv'])) }}" class="btn btn-success btn-sm">
                <i class="fas fa-file-csv"></i> Экспорт CSV
            </a>
        </x-slot>
    </x-page-header>

    @if(session('success'))<div class="alert alert-success" style="border-radius:12px;"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>@endif
    @if(session('info'))<div class="alert alert-info" style="border-radius:12px;"><i class="fas fa-info-circle"></i> {{ session('info') }}</div>@endif

    {{-- Stat cards --}}
    <div class="row">
        <div class="col-md-3 col-6 mb-3">
            <x-stat-card label="Всего анкет" value="{{ number_format($summary['total']) }}" hint="за период" variant="primary" icon="poll"/>
        </div>
        <div class="col-md-3 col-6 mb-3">
            <x-stat-card label="Avg score" value="{{ number_format($summary['avg_score'], 2) }}"
                hint="из 4 (yes-ответы)"
                variant="{{ ph_score_variant($summary['avg_score']) }}" icon="star"/>
        </div>
        <div class="col-md-3 col-6 mb-3">
            <x-stat-card label="Решено на звонке" value="{{ number_format($summary['solved']) }}"
                hint="{{ $summary['total'] > 0 ? round($summary['solved']/$summary['total']*100) : 0 }}%"
                variant="success" icon="check-circle"/>
        </div>
        <div class="col-md-3 col-6 mb-3">
            <x-stat-card label="С комментарием" value="{{ number_format($summary['with_complaint']) }}"
                hint="{{ $summary['total'] > 0 ? round($summary['with_complaint']/$summary['total']*100) : 0 }}%"
                variant="info" icon="comment"/>
        </div>
    </div>

    {{-- Score distribution + per-question rates --}}
    <div class="row">
        <div class="col-lg-6 mb-3">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title mb-0" style="font-weight:600;font-size:1rem;">Распределение оценок (yes-count)</h3>
                </div>
                <div class="card-body">
                    @php $maxD = max($distribution); $maxD = $maxD ?: 1; @endphp
                    @for($s = 4; $s >= 0; $s--)
                        @php
                            $cnt = $distribution[$s] ?? 0;
                            $pct = $summary['total'] > 0 ? round($cnt / $summary['total'] * 100, 1) : 0;
                            $vw = round($cnt / $maxD * 100);
                            $var = $s >= 3 ? 'success' : ($s === 2 ? 'warning' : ($s >= 1 ? 'danger' : 'muted'));
                        @endphp
                        <div class="d-flex align-items-center mb-2" style="gap:10px;">
                            <span class="ph-badge is-{{ $var }}" style="min-width:48px;justify-content:center;">{{ $s }}/4</span>
                            <div style="flex:1;background:#f1f5f9;border-radius:8px;overflow:hidden;height:18px;">
                                <div style="width:{{ $vw }}%;background:var(--ph-{{ $var === 'muted' ? 'muted' : $var }});height:100%;"></div>
                            </div>
                            <span class="text-muted small" style="min-width:80px;text-align:right;">{{ $cnt }} ({{ $pct }}%)</span>
                        </div>
                    @endfor
                </div>
            </div>
        </div>
        <div class="col-lg-6 mb-3">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title mb-0" style="font-weight:600;font-size:1rem;">% положительных по вопросам</h3>
                </div>
                <div class="card-body">
                    @foreach(['q1','q2','q3','q4'] as $i => $q)
                        @php
                            $pct = $summary['pct_'.$q.'_yes'];
                            $cntY = $summary['count_'.$q.'_yes'];
                            $cntN = $summary['count_'.$q.'_no'];
                            $var = $pct >= 75 ? 'success' : ($pct >= 50 ? 'warning' : 'danger');
                        @endphp
                        <div class="d-flex align-items-center mb-2" style="gap:10px;">
                            <span class="ph-badge is-primary" style="min-width:48px;justify-content:center;">Q{{ $i+1 }}</span>
                            <div style="flex:1;background:#f1f5f9;border-radius:8px;overflow:hidden;height:18px;">
                                <div style="width:{{ $pct }}%;background:var(--ph-{{ $var }});height:100%;"></div>
                            </div>
                            <span class="text-muted small" style="min-width:90px;text-align:right;">
                                <span style="color:#16a34a;">{{ $cntY }}</span> / <span style="color:#dc2626;">{{ $cntN }}</span>
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <form action="{{ route('feedback.all') }}" method="get" class="ph-filter-card">
        <div class="ph-filter-row">
            <div>
                <label>От</label>
                <input type="date" name="from_date" value="{{ $from_date }}" class="form-control form-control-sm">
            </div>
            <div>
                <label>До</label>
                <input type="date" name="to_date" value="{{ $to_date }}" class="form-control form-control-sm">
            </div>
            <div>
                <label>Компания</label>
                <select name="gateway" class="form-control form-control-sm">
                    <option value="">Все</option>
                    @foreach(\App\Services\GatewayService::options() as $gw => $label)
                        <option value="{{ $gw }}" {{ (string)($gateway ?? '') === (string)$gw ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label>Score</label>
                <select name="score_filter" class="form-control form-control-sm">
                    <option value="">Любой</option>
                    @for($s = 0; $s <= 4; $s++)
                        <option value="{{ $s }}" {{ (string)($scoreFilter ?? '') === (string)$s ? 'selected' : '' }}>{{ $s }}/4</option>
                    @endfor
                </select>
            </div>
            <div>
                <label>Комментарий</label>
                <select name="has_comment" class="form-control form-control-sm">
                    <option value="">Любой</option>
                    <option value="yes" {{ ($hasComment ?? '') === 'yes' ? 'selected' : '' }}>Есть</option>
                    <option value="no" {{ ($hasComment ?? '') === 'no' ? 'selected' : '' }}>Нет</option>
                </select>
            </div>
        </div>
        <div class="ph-filter-actions">
            <a href="{{ route('feedback.all') }}" class="btn btn-light btn-sm"><i class="fas fa-undo"></i> Сбросить</a>
            <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-filter"></i> Применить</button>
        </div>
    </form>

    {{-- Table --}}
    <div class="card mt-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0" style="font-weight:600;font-size:1rem;">Анкеты ({{ $allFeedback->count() }})</h3>
        </div>
        <div class="card-body p-0">
            @if($allFeedback->isEmpty())
                <div class="ph-empty">
                    <div class="ph-empty-icon"><i class="fas fa-inbox"></i></div>
                    <h4>Анкет нет</h4>
                    <div class="text-muted small">За выбранный период анкет не получено.</div>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Дата</th>
                                <th>Компания</th>
                                <th>Оператор</th>
                                <th>Клиент</th>
                                <th>Score</th>
                                <th>Q1</th><th>Q2</th><th>Q3</th><th>Q4</th>
                                <th>Решено</th>
                                <th style="min-width:240px;">Комментарий</th>
                                <th>Звонок</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($allFeedback as $data)
                                @php
                                    $score = ((int)$data->q1 === 1) + ((int)$data->q2 === 1) + ((int)$data->q3 === 1) + ((int)$data->q4 === 1);
                                    $sVar = $score >= 3 ? 'success' : ($score === 2 ? 'warning' : 'danger');
                                    $gw = optional($data->call)->gateway;
                                    $gwInfo = \App\Services\GatewayService::info($gw);
                                @endphp
                                <tr>
                                    <td><small class="text-muted">#{{ $data->id }}</small></td>
                                    <td><small>{{ \Carbon\Carbon::parse($data->created_at)->format('d.m H:i') }}</small></td>
                                    <td><span class="ph-badge is-info">{{ $gwInfo['short'] }}</span></td>
                                    <td><small>{{ optional(optional($data->call)->operator)->name ?: '—' }}</small></td>
                                    <td><small>{{ ph_format_phone(optional($data->call)->client_telephone ?: '') }}</small></td>
                                    <td><span class="ph-badge is-{{ $sVar }}"><i class="fas fa-star"></i> {{ $score }}/4</span></td>
                                    @foreach(['q1','q2','q3','q4'] as $q)
                                        <td>
                                            @if((int)$data->{$q} === 1)
                                                <span style="color:#16a34a;font-weight:bold;"><i class="fas fa-check"></i></span>
                                            @elseif((int)$data->{$q} === -1)
                                                <span style="color:#dc2626;font-weight:bold;"><i class="fas fa-times"></i></span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                    @endforeach
                                    <td>
                                        @if((int)$data->solved === 1)
                                            <span class="ph-badge is-success"><i class="fas fa-check"></i></span>
                                        @else
                                            <span class="ph-badge is-muted">—</span>
                                        @endif
                                    </td>
                                    <td style="white-space: normal;">
                                        @if(!empty($data->complaint))
                                            <small>{{ ph_short_text($data->complaint, 100) }}</small>
                                        @else
                                            <small class="text-muted">—</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($data->call)
                                            <a href="{{ route('admin.report.calls', ['phone' => $data->call->client_telephone, 'from_date' => date('Y-m-d', strtotime($data->call->created_at)), 'to_date' => date('Y-m-d', strtotime($data->call->created_at))]) }}"
                                               class="ph-badge is-primary" title="К звонку"><i class="fas fa-phone"></i> #{{ $data->call->id }}</a>
                                        @else
                                            <small class="text-muted">—</small>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

</div>
@endsection
