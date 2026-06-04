@extends('admin.layouts.index')

@section('content')
<div class="content-wrapper p-3 p-md-4">

    <x-page-header title="Дашборд"
                   subtitle="Сводка работы за {{ \Carbon\Carbon::parse($date)->isoFormat('D MMMM YYYY') }}"
                   :breadcrumbs="[
                       ['label' => 'Главная', 'url' => route('admin.bigreport')],
                       ['label' => 'Дашборд'],
                   ]">
        <x-slot name="actions">
            <form method="GET" action="{{ url('/admin/dashboard') }}" class="d-flex flex-wrap" style="gap:8px;">
                <input type="date" name="date" value="{{ $date }}" class="form-control form-control-sm" style="width:160px;">
                <select name="gateway" class="form-control form-control-sm" style="width:160px;">
                    <option value="">Все компании</option>
                    @foreach($gatewayOptions as $val => $label)
                        <option value="{{ $val }}" {{ (string)$gateway === (string)$val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="fas fa-sync"></i> Обновить
                </button>
            </form>
        </x-slot>
    </x-page-header>

    @if(session('success'))
        <div class="alert alert-success" style="border-radius:12px;"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger" style="border-radius:12px;"><i class="fas fa-times-circle"></i> {{ session('error') }}</div>
    @endif

    {{-- Row 1: calls overview --}}
    <div class="row">
        <div class="col-md-3 col-6 mb-3">
            <x-stat-card label="Звонки сегодня" value="{{ number_format($daily['total']) }}" hint="всего за день" variant="primary" icon="phone"/>
        </div>
        <div class="col-md-3 col-6 mb-3">
            <x-stat-card label="Входящие" value="{{ number_format($daily['inbound']) }}"
                hint="{{ $daily['total'] > 0 ? round($daily['inbound'] / $daily['total'] * 100) : 0 }}% от всех"
                variant="info" icon="arrow-down"/>
        </div>
        <div class="col-md-3 col-6 mb-3">
            <x-stat-card label="Исходящие" value="{{ number_format($daily['outbound']) }}"
                hint="{{ $daily['total'] > 0 ? round($daily['outbound'] / $daily['total'] * 100) : 0 }}% от всех"
                variant="primary" icon="arrow-up"/>
        </div>
        <div class="col-md-3 col-6 mb-3">
            <x-stat-card label="Пропущенные" value="{{ number_format($daily['missed']) }}"
                hint="{{ $daily['total'] > 0 ? round($daily['missed'] / max($daily['total'],1) * 100, 1) : 0 }}% от всех"
                variant="{{ $daily['total'] > 0 && $daily['missed'] / max($daily['total'],1) > 0.15 ? 'danger' : 'warning' }}"
                icon="phone-slash"/>
        </div>
    </div>

    {{-- Row 2: quality + comms --}}
    <div class="row">
        <div class="col-md-3 col-6 mb-3">
            <x-stat-card label="Avg duration" value="{{ ph_format_duration($daily['avg_duration']) }}" hint="средняя длительность" variant="muted" icon="stopwatch"/>
        </div>
        <div class="col-md-3 col-6 mb-3">
            <x-stat-card label="Avg score" value="{{ number_format($fbSummary['avg_score'], 2) }}" hint="из 4 (yes-ответы)" variant="{{ ph_score_variant($fbSummary['avg_score']) }}" icon="star"/>
        </div>
        <div class="col-md-3 col-6 mb-3">
            <x-stat-card label="SMS отправлено" value="{{ number_format($smsCounters['sent']) }}" hint="{{ number_format($smsCounters['pending']) }} в очереди ({{ $smsCounters['rate'] }}%)" variant="success" icon="comment-dots"/>
        </div>
        <div class="col-md-3 col-6 mb-3">
            <x-stat-card label="Анкет получено" value="{{ number_format($fbSummary['total']) }}" hint="{{ $fbSummary['with_complaint'] }} с комментарием" variant="info" icon="poll"/>
        </div>
    </div>

    {{-- Row 3: conversion + operators --}}
    <div class="row">
        <div class="col-md-4 col-6 mb-3">
            <x-stat-card label="Конверсия SMS → анкета" value="{{ $conv['feedback_rate'] }}%" hint="из {{ $conv['sms_sent'] }} SMS пришло {{ $conv['feedback_received'] }} анкет" variant="primary" icon="funnel-dollar"/>
        </div>
        <div class="col-md-4 col-6 mb-3">
            <x-stat-card label="Операторов онлайн" value="{{ $live['online'] }}" hint="{{ $live['idle'] }} свободно · {{ $live['on_call'] }} в разговоре" variant="success" icon="users"/>
        </div>
        <div class="col-md-4 col-12 mb-3">
            <x-stat-card label="В разговоре" value="{{ $live['on_call'] }}" hint="{{ $live['offline'] }} офлайн" variant="warning" icon="headset"/>
        </div>
    </div>

    {{-- Row 4: gateway breakdown + live operators table --}}
    <div class="row">
        <div class="col-lg-6 mb-3">
            <div class="card">
                <div class="card-header"><h3 class="card-title mb-0" style="font-weight:600;font-size:1rem;">Звонки по компаниям сегодня</h3></div>
                <div class="card-body p-0">
                    @if(empty($byGateway))
                        <div class="ph-empty">
                            <div class="ph-empty-icon"><i class="fas fa-inbox"></i></div>
                            <h4>Нет звонков</h4>
                            <div class="text-muted small">За выбранный день звонков нет.</div>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead>
                                    <tr><th>Компания</th><th class="text-right">Всего</th><th class="text-right">Отвечено</th><th class="text-right">Пропущено</th></tr>
                                </thead>
                                <tbody>
                                    @foreach($byGateway as $row)
                                        <tr>
                                            <td>
                                                <strong>{{ $row['name'] }}</strong>
                                                <small class="text-muted d-block">{{ $row['gateway'] }}</small>
                                            </td>
                                            <td class="text-right">{{ number_format($row['total']) }}</td>
                                            <td class="text-right"><span class="ph-badge is-success"><i class="fas fa-check"></i> {{ $row['answered'] }}</span></td>
                                            <td class="text-right">
                                                @if($row['missed'] > 0)
                                                    <span class="ph-badge is-danger"><i class="fas fa-times"></i> {{ $row['missed'] }}</span>
                                                @else
                                                    <span class="text-muted">—</span>
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

        <div class="col-lg-6 mb-3">
            <div class="card">
                <div class="card-header"><h3 class="card-title mb-0" style="font-weight:600;font-size:1rem;">Операторы — текущий статус</h3></div>
                <div class="card-body p-0">
                    @php
                        $visible = array_values(array_filter($liveOperators, function($o){ return $o['status'] !== 'offline'; }));
                    @endphp
                    @if(empty($visible))
                        <div class="ph-empty">
                            <div class="ph-empty-icon"><i class="fas fa-user-clock"></i></div>
                            <h4>Никого нет онлайн</h4>
                            <div class="text-muted small">Операторов в системе: {{ count($liveOperators) }} · все офлайн.</div>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead>
                                    <tr><th>Оператор</th><th>Статус</th><th class="text-right">Активность</th></tr>
                                </thead>
                                <tbody>
                                    @foreach($visible as $op)
                                        <tr>
                                            <td>
                                                <span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:{{ $op['color'] }};margin-right:6px;"></span>
                                                <strong>{{ $op['name'] }}</strong>
                                            </td>
                                            <td>
                                                @php
                                                    $st = $op['status'];
                                                    $stMap = ['on_call'=>['warning','headset','В разговоре'],'idle'=>['info','coffee','Свободен'],'online'=>['success','circle','Онлайн']];
                                                    $cfg = $stMap[$st] ?? ['muted','circle',$st];
                                                @endphp
                                                <span class="ph-badge is-{{ $cfg[0] }}"><i class="fas fa-{{ $cfg[1] }}"></i> {{ $cfg[2] }}</span>
                                            </td>
                                            <td class="text-right text-muted small">{{ $op['last_seen_human'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
                <div class="card-footer bg-transparent">
                    <small class="text-muted">
                        Всего: {{ count($liveOperators) }} ·
                        онлайн: {{ $live['online'] }} ·
                        в разговоре: {{ $live['on_call'] }} ·
                        офлайн: {{ $live['offline'] }}
                    </small>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
