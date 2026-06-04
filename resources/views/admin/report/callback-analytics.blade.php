@extends('admin.layouts.index')

@php
    $summary = $analytics['summary'] ?? [];
    $charts = $analytics['charts'] ?? [];
    $operatorRows = $analytics['operator_rows'] ?? [];
    $recentMissedRows = $analytics['recent_missed_rows'] ?? [];
    $period = $analytics['period'] ?? [];
    $activePreset = $activePreset ?? '';
@endphp

@section('content')
<script src="https://cdn.jsdelivr.net/npm/chart.js@2.9.4/dist/Chart.min.js"></script>

<div class="content-wrapper p-3 p-md-4">

    <x-page-header title="Аналитика звонков"
                   subtitle="Период: {{ $from_date }} — {{ $to_date }} · поиск перезвонов до {{ $period['search_end'] ?? '' }}"
                   :breadcrumbs="[
                       ['label' => 'Главная', 'url' => route('admin.bigreport')],
                       ['label' => 'Аналитика звонков'],
                   ]">
        <x-slot name="actions">
            <a href="{{ route('admin.report.calls') }}" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-list"></i> К звонкам
            </a>
        </x-slot>
    </x-page-header>

    {{-- Filter card with date inputs + presets --}}
    <form action="{{ route('admin.report.callback-analytics') }}" method="get" class="ph-filter-card">
        <div class="d-flex flex-wrap mb-2" style="gap:6px;">
            @php $presets = ['today'=>'Сегодня','yesterday'=>'Вчера','week'=>'Неделя','month'=>'Месяц','previous_month'=>'Прошлый месяц']; @endphp
            @foreach($presets as $key => $label)
                <button type="submit" name="preset" value="{{ $key }}"
                        class="btn btn-sm {{ $activePreset === $key ? 'btn-primary' : 'btn-light' }}"
                        style="border-radius:999px;">
                    {{ $label }}
                </button>
            @endforeach
        </div>
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
                <label>Продукт</label>
                <select name="gateway" class="form-control form-control-sm">
                    <option value="">Все</option>
                    @foreach(\App\Services\GatewayService::options() as $gw => $label)
                        <option value="{{ $gw }}" {{ (string)($gateway ?? '') === (string)$gw ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="ph-filter-actions">
            <a href="{{ route('admin.report.callback-analytics') }}" class="btn btn-light btn-sm">
                <i class="fas fa-undo"></i> Сбросить
            </a>
            <button type="submit" class="btn btn-primary btn-sm">
                <i class="fas fa-sync"></i> Обновить
            </button>
        </div>
    </form>

    {{-- Top stat cards --}}
    <div class="row mt-3">
        <div class="col-md-3 col-6 mb-3">
            <x-stat-card label="Пропущено" value="{{ number_format($summary['missed_inbound_calls'] ?? 0) }}"
                hint="входящих звонков" variant="danger" icon="phone-slash"/>
        </div>
        <div class="col-md-3 col-6 mb-3">
            <x-stat-card label="Перезвонили" value="{{ number_format($summary['resolved_missed_calls'] ?? 0) }}"
                hint="{{ $summary['resolution_rate_percent'] ?? 0 }}% от пропущенных"
                variant="success" icon="phone-volume"/>
        </div>
        <div class="col-md-3 col-6 mb-3">
            <x-stat-card label="Не закрыто" value="{{ number_format($summary['unresolved_missed_calls'] ?? 0) }}"
                hint="ждут контакта"
                variant="{{ ($summary['unresolved_missed_calls'] ?? 0) > 0 ? 'warning' : 'muted' }}"
                icon="exclamation-triangle"/>
        </div>
        <div class="col-md-3 col-6 mb-3">
            <x-stat-card label="Avg callback" value="{{ $summary['avg_working_callback_human'] ?? '0:00' }}"
                hint="рабочее время до перезвона"
                variant="primary" icon="stopwatch"/>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3 col-6 mb-3">
            <x-stat-card label="Всего звонков" value="{{ number_format($summary['total_calls'] ?? 0) }}"
                hint="за период" variant="primary" icon="phone"/>
        </div>
        <div class="col-md-3 col-6 mb-3">
            <x-stat-card label="С разговором" value="{{ number_format($summary['talked_calls'] ?? 0) }}"
                hint="dialog_duration > 0" variant="info" icon="comments"/>
        </div>
        <div class="col-md-3 col-6 mb-3">
            <x-stat-card label="Avg разговор" value="{{ $summary['avg_talk_human'] ?? '0:00' }}"
                hint="среднее время разговора" variant="muted" icon="hourglass-half"/>
        </div>
        <div class="col-md-3 col-6 mb-3">
            <x-stat-card label="Total talk" value="{{ $summary['total_talk_human'] ?? '0:00' }}"
                hint="суммарно за период" variant="muted" icon="clock"/>
        </div>
    </div>

    {{-- SLA card --}}
    <div class="card mb-3">
        <div class="card-header">
            <h3 class="card-title mb-0" style="font-weight:600;font-size:1rem;">SLA по перезвонам (от пропущенных, закрытых перезвоном)</h3>
        </div>
        <div class="card-body">
            @php
                $slas = [
                    ['label'=>'≤ 5 минут',  'pct'=>$summary['sla_5_percent']  ?? 0, 'var'=>'success'],
                    ['label'=>'≤ 15 минут', 'pct'=>$summary['sla_15_percent'] ?? 0, 'var'=>'info'],
                    ['label'=>'≤ 30 минут', 'pct'=>$summary['sla_30_percent'] ?? 0, 'var'=>'warning'],
                ];
            @endphp
            @foreach($slas as $sla)
                <div class="d-flex align-items-center mb-2" style="gap:10px;">
                    <span class="ph-badge is-{{ $sla['var'] }}" style="min-width:120px;justify-content:center;">{{ $sla['label'] }}</span>
                    <div style="flex:1;background:#f1f5f9;border-radius:8px;overflow:hidden;height:20px;">
                        <div style="width:{{ $sla['pct'] }}%;background:var(--ph-{{ $sla['var'] }});height:100%;"></div>
                    </div>
                    <span class="text-muted small" style="min-width:60px;text-align:right;font-weight:600;">{{ $sla['pct'] }}%</span>
                </div>
            @endforeach
            <small class="text-muted">Avg raw delay: {{ $summary['avg_raw_callback_human'] ?? '0:00' }} (без учёта рабочих часов)</small>
        </div>
    </div>

    {{-- Charts --}}
    <div class="row">
        <div class="col-12 mb-3">
            <div class="card">
                <div class="card-header"><h3 class="card-title mb-0" style="font-weight:600;font-size:1rem;">Звонки по часам</h3></div>
                <div class="card-body" style="height:300px;"><canvas id="callsByHourChart" height="110"></canvas></div>
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <div class="card">
                <div class="card-header"><h3 class="card-title mb-0" style="font-weight:600;font-size:1rem;">По дням недели</h3></div>
                <div class="card-body" style="height:300px;"><canvas id="callsByWeekdayChart" height="230"></canvas></div>
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <div class="card">
                <div class="card-header"><h3 class="card-title mb-0" style="font-weight:600;font-size:1rem;">По числам месяца</h3></div>
                <div class="card-body" style="height:300px;"><canvas id="callsByMonthdayChart" height="230"></canvas></div>
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <div class="card">
                <div class="card-header"><h3 class="card-title mb-0" style="font-weight:600;font-size:1rem;">Распределение задержек перезвона</h3></div>
                <div class="card-body" style="height:300px;"><canvas id="callbackBucketsChart" height="230"></canvas></div>
            </div>
        </div>
    </div>

    {{-- Operator rows --}}
    <div class="card mb-3">
        <div class="card-header">
            <h3 class="card-title mb-0" style="font-weight:600;font-size:1rem;">По операторам</h3>
        </div>
        <div class="card-body p-0">
            @if(empty($operatorRows))
                <div class="ph-empty">
                    <div class="ph-empty-icon"><i class="fas fa-user-slash"></i></div>
                    <h4>Нет данных</h4>
                </div>
            @else
                <div class="table-responsive">
                    <table id="callback_operator_table" class="table table-sm table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Оператор</th>
                                <th class="text-right">Всего</th>
                                <th class="text-right">С разговором</th>
                                <th class="text-right">Пропущено</th>
                                <th class="text-right">Закрыто</th>
                                <th class="text-right">Avg callback</th>
                                <th class="text-right">Avg разговор</th>
                                <th class="text-right">Total talk</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($operatorRows as $row)
                                <tr>
                                    <td>
                                        <strong>{{ $row['operator_name'] }}</strong>
                                        @if(!empty($row['operator_phone']))
                                            <small class="text-muted d-block">{{ $row['operator_phone'] }}</small>
                                        @endif
                                    </td>
                                    <td class="text-right">{{ number_format($row['total_calls']) }}</td>
                                    <td class="text-right">{{ number_format($row['talked_calls']) }}</td>
                                    <td class="text-right">
                                        @if(($row['missed_received'] ?? 0) > 0)
                                            <span class="ph-badge is-danger">{{ $row['missed_received'] }}</span>
                                        @else
                                            <small class="text-muted">0</small>
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        @if(($row['resolved_missed'] ?? 0) > 0)
                                            <span class="ph-badge is-success">{{ $row['resolved_missed'] }}</span>
                                        @else
                                            <small class="text-muted">0</small>
                                        @endif
                                    </td>
                                    <td class="text-right"><small>{{ $row['avg_callback_human'] }}</small></td>
                                    <td class="text-right"><small>{{ $row['avg_talk_human'] }}</small></td>
                                    <td class="text-right"><small>{{ $row['total_talk_human'] }}</small></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    {{-- Recent missed --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title mb-0" style="font-weight:600;font-size:1rem;">Последние пропущенные и их закрытие</h3>
        </div>
        <div class="card-body p-0">
            @if(empty($recentMissedRows))
                <div class="ph-empty">
                    <div class="ph-empty-icon"><i class="fas fa-check-circle"></i></div>
                    <h4>Нет пропущенных</h4>
                    <div class="text-muted small">За выбранный период пропусков не зафиксировано.</div>
                </div>
            @else
                <div class="table-responsive">
                    <table id="callback_recent_table" class="table table-sm table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Телефон</th>
                                <th>Пропущен</th>
                                <th>Кто пропустил</th>
                                <th>Статус</th>
                                <th>Кто закрыл</th>
                                <th>Тип закрытия</th>
                                <th class="text-right">Рабочий delay</th>
                                <th class="text-right">Raw delay</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentMissedRows as $row)
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.report.calls', ['phone' => $row['phone']]) }}">
                                            <small>{{ ph_format_phone($row['phone']) }}</small>
                                        </a>
                                    </td>
                                    <td><small>{{ $row['missed_at'] }}</small></td>
                                    <td><small>{{ $row['missed_operator_name'] ?: '—' }}</small></td>
                                    <td>
                                        @if($row['is_resolved'])
                                            <span class="ph-badge is-success"><i class="fas fa-check"></i> Закрыт</span>
                                        @else
                                            <span class="ph-badge is-warning"><i class="fas fa-hourglass-half"></i> Ждёт</span>
                                        @endif
                                    </td>
                                    <td><small>{{ $row['resolved_operator_name'] ?: '—' }}</small></td>
                                    <td><small>{{ $row['closure_type'] ?: '—' }}</small></td>
                                    <td class="text-right"><small>{{ $row['working_delay_human'] ?: '—' }}</small></td>
                                    <td class="text-right"><small>{{ $row['raw_delay_human'] ?: '—' }}</small></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

</div>

<script type="application/json" id="callbackAnalyticsCharts">@json($charts)</script>
<script>
    function callbackAnalyticsChart(id, type, data, options) {
        var canvas = document.getElementById(id);
        if (!canvas) return null;
        return new Chart(canvas, {
            type: type,
            data: data,
            options: options || { responsive: true, maintainAspectRatio: false }
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        var payloadNode = document.getElementById('callbackAnalyticsCharts');
        if (!payloadNode) return;
        var charts = JSON.parse(payloadNode.textContent || '{}');

        callbackAnalyticsChart('callsByHourChart', 'bar', {
            labels: charts.hours.labels,
            datasets: [
                { label: 'Все звонки', data: charts.hours.total, backgroundColor: 'rgba(59, 130, 246, 0.75)', borderRadius: 6 },
                { label: 'Разговоры', data: charts.hours.talked, backgroundColor: 'rgba(16, 185, 129, 0.75)', borderRadius: 6 },
                { label: 'Пропущенные', data: charts.hours.missed, backgroundColor: 'rgba(239, 68, 68, 0.75)', borderRadius: 6 }
            ]
        }, { responsive: true, maintainAspectRatio: false, scales: { yAxes: [{ ticks: { beginAtZero: true, precision: 0 } }] } });

        callbackAnalyticsChart('callsByWeekdayChart', 'line', {
            labels: charts.weekdays.labels,
            datasets: [
                { label: 'Все звонки', data: charts.weekdays.total, borderColor: '#2563eb', backgroundColor: 'rgba(37, 99, 235, 0.16)', fill: true },
                { label: 'Пропущенные', data: charts.weekdays.missed, borderColor: '#dc2626', backgroundColor: 'rgba(220, 38, 38, 0.08)', fill: true }
            ]
        }, { responsive: true, maintainAspectRatio: false, scales: { yAxes: [{ ticks: { beginAtZero: true, precision: 0 } }] } });

        callbackAnalyticsChart('callsByMonthdayChart', 'bar', {
            labels: charts.monthdays.labels,
            datasets: [
                { label: 'Все звонки', data: charts.monthdays.total, backgroundColor: 'rgba(14, 165, 233, 0.7)' },
                { label: 'Разговоры', data: charts.monthdays.talked, backgroundColor: 'rgba(34, 197, 94, 0.7)' }
            ]
        }, { responsive: true, maintainAspectRatio: false, scales: { yAxes: [{ ticks: { beginAtZero: true, precision: 0 } }] } });

        callbackAnalyticsChart('callbackBucketsChart', 'doughnut', {
            labels: charts.callback_buckets.labels,
            datasets: [{ data: charts.callback_buckets.values, backgroundColor: ['#16a34a', '#65a30d', '#f59e0b', '#f97316', '#dc2626'] }]
        }, { responsive: true, maintainAspectRatio: false, legend: { position: 'bottom' } });

        if (window.jQuery && window.jQuery.fn && window.jQuery.fn.DataTable) {
            window.jQuery('#callback_operator_table').DataTable({
                stateSave: true, paging: true, pageLength: 25, ordering: true, searching: true, autoWidth: false, responsive: true
            });
            window.jQuery('#callback_recent_table').DataTable({
                stateSave: true, paging: true, pageLength: 25, ordering: true, searching: true, autoWidth: false, responsive: true,
                order: [[1, 'desc']]
            });
        }
    });
</script>
@endsection
