@extends('operator.layouts.index')

@section('content')
<div class="content-wrapper p-3 p-md-4">

    <x-page-header title="Моё окно"
                   subtitle="{{ $workspaceData['operator']['name'] ?? 'Оператор' }} · {{ \Carbon\Carbon::now()->isoFormat('D MMMM YYYY') }}">
        <x-slot name="actions">
            <a href="{{ route('operator.report.calls') }}" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-list"></i> Все мои звонки
            </a>
        </x-slot>
    </x-page-header>

    {{-- Top stat cards (4) --}}
    <div class="row" id="workspace-stats">
        <div class="col-md-3 col-6 mb-3">
            <div class="ph-stat-card is-primary">
                <span class="ph-stat-icon"><i class="fas fa-phone"></i></span>
                <div class="ph-stat-label">Звонков сегодня</div>
                <div class="ph-stat-value" id="stat_total_calls">{{ $workspaceData['stats']['today_total_calls'] ?? 0 }}</div>
                <div class="ph-stat-hint">
                    <span id="stat_inbound">{{ $workspaceData['stats']['today_inbound_calls'] ?? 0 }}</span> вх /
                    <span id="stat_outbound">{{ $workspaceData['stats']['today_outbound_calls'] ?? 0 }}</span> исх
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6 mb-3">
            <div class="ph-stat-card is-danger">
                <span class="ph-stat-icon"><i class="fas fa-phone-slash"></i></span>
                <div class="ph-stat-label">Пропущено</div>
                <div class="ph-stat-value">{{ $extra['missed_today'] ?? 0 }}</div>
                <div class="ph-stat-hint">сегодня</div>
            </div>
        </div>
        <div class="col-md-3 col-6 mb-3">
            <div class="ph-stat-card is-{{ ph_score_variant($extra['avg_score'] ?? 0) }}">
                <span class="ph-stat-icon"><i class="fas fa-star"></i></span>
                <div class="ph-stat-label">Avg score</div>
                <div class="ph-stat-value">{{ number_format($extra['avg_score'] ?? 0, 2) }}</div>
                <div class="ph-stat-hint">из 4 · {{ $extra['fb_count_today'] ?? 0 }} анкет</div>
            </div>
        </div>
        <div class="col-md-3 col-6 mb-3">
            <div class="ph-stat-card is-info">
                <span class="ph-stat-icon"><i class="fas fa-stopwatch"></i></span>
                <div class="ph-stat-label">Рабочее время</div>
                <div class="ph-stat-value">{{ ph_format_duration_human($extra['working_seconds'] ?? 0) }}</div>
                <div class="ph-stat-hint">сегодня · ср. разговор <span id="stat_avg_talk">{{ $workspaceData['stats']['today_avg_talk_human'] ?? '0:00' }}</span></div>
            </div>
        </div>
    </div>

    {{-- Row: live status + secondary metrics --}}
    <div class="row">
        <div class="col-xl-8 mb-3">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title mb-0" style="font-weight:600;font-size:1rem;">Текущее состояние</h3>
                </div>
                <div class="card-body">
                    <div id="workspace_live_summary">
                        @if(!empty($workspaceData['popup_call']))
                            <div class="alert alert-info mb-0" style="border-radius:12px;">
                                <strong>{{ $workspaceData['popup_call']['title'] }}</strong><br>
                                {{ $workspaceData['popup_call']['message'] }}
                            </div>
                        @else
                            <div class="text-muted">Активных звонков и ожидающих анкет сейчас нет.</div>
                        @endif
                    </div>
                </div>
                <div class="card-footer bg-transparent text-muted small">
                    Обновлено: <span id="workspace_generated_at">{{ $workspaceData['generated_at'] ?? '' }}</span>
                </div>
            </div>
        </div>

        <div class="col-xl-4 mb-3">
            <div class="card">
                <div class="card-header"><h3 class="card-title mb-0" style="font-weight:600;font-size:1rem;">Сегодня</h3></div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-7 text-muted">С разговорами</dt>
                        <dd class="col-5 text-right" id="stat_talked">{{ $workspaceData['stats']['today_talked_calls'] ?? 0 }}</dd>
                        <dt class="col-7 text-muted">Анкет заполнено</dt>
                        <dd class="col-5 text-right" id="stat_surveyed_calls">{{ $workspaceData['stats']['today_surveyed_calls'] ?? 0 }}</dd>
                        <dt class="col-7 text-muted">Ждут анкету</dt>
                        <dd class="col-5 text-right" id="stat_pending_surveys">{{ $workspaceData['stats']['today_pending_surveys'] ?? 0 }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    {{-- 14-day personal trend --}}
    @if(!empty($extra['recent_days']))
        @php $maxDay = max(array_map(function($d){ return $d['total']; }, $extra['recent_days'])); $maxDay = $maxDay ?: 1; @endphp
        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title mb-0" style="font-weight:600;font-size:1rem;">Личная динамика — последние 14 дней</h3>
            </div>
            <div class="card-body">
                <div style="display:grid;grid-template-columns:repeat(14,1fr);gap:6px;align-items:end;height:140px;">
                    @foreach($extra['recent_days'] as $day)
                        @php
                            $h = round(($day['total'] / $maxDay) * 100);
                            $h = max($h, 4);
                            $aH = $day['total'] > 0 ? round(($day['answered'] / $day['total']) * 100) : 0;
                        @endphp
                        <div style="height:100%;display:flex;flex-direction:column;justify-content:flex-end;align-items:center;gap:4px;" title="{{ $day['date'] }}: {{ $day['total'] }} (из них {{ $day['answered'] }} отв.)">
                            <span class="small text-muted">{{ $day['total'] ?: '' }}</span>
                            <div style="width:80%;height:{{ $h }}%;background:linear-gradient(180deg,var(--ph-primary) 0%,var(--ph-primary-soft) 100%);border-radius:6px;position:relative;overflow:hidden;">
                                <div style="position:absolute;bottom:0;left:0;right:0;height:{{ $aH }}%;background:var(--ph-success);"></div>
                            </div>
                            <span class="small text-muted" style="font-size:0.65rem;">{{ \Carbon\Carbon::parse($day['date'])->format('d.m') }}</span>
                        </div>
                    @endforeach
                </div>
                <div class="d-flex justify-content-end mt-2" style="gap:14px;font-size:0.78rem;color:var(--ph-text-soft);">
                    <span><span style="display:inline-block;width:10px;height:10px;background:var(--ph-success);border-radius:2px;margin-right:4px;"></span>Отвеченные</span>
                    <span><span style="display:inline-block;width:10px;height:10px;background:var(--ph-primary-soft);border-radius:2px;margin-right:4px;"></span>Все звонки</span>
                </div>
            </div>
        </div>
    @endif

    {{-- Recent calls + recent surveys (JS-rendered) --}}
    <div class="row">
        <div class="col-xl-7 mb-3">
            <div class="card">
                <div class="card-header"><h3 class="card-title mb-0" style="font-weight:600;font-size:1rem;">Последние звонки</h3></div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Телефон</th>
                                    <th>Дата</th>
                                    <th>Разговор</th>
                                    <th>Анкета</th>
                                </tr>
                            </thead>
                            <tbody id="workspace_recent_calls"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-5 mb-3">
            <div class="card">
                <div class="card-header"><h3 class="card-title mb-0" style="font-weight:600;font-size:1rem;">Последние анкеты</h3></div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Звонок</th>
                                    <th>Причина</th>
                                    <th>Статус</th>
                                </tr>
                            </thead>
                            <tbody id="workspace_recent_surveys"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="application/json" id="operatorWorkspaceInitialData">@json($workspaceData)</script>
<script>
function escapeHtml(value) {
    return String(value || '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function renderWorkspaceSummary(data) {
    var summary = document.getElementById('workspace_live_summary');
    if (!summary) return;
    if (data.popup_call) {
        var colorClass = data.popup_call.is_active ? 'alert-danger' : 'alert-warning';
        summary.innerHTML = '<div class="alert ' + colorClass + ' mb-0" style="border-radius:12px;">'
            + '<strong>' + escapeHtml(data.popup_call.title) + '</strong><br>'
            + escapeHtml(data.popup_call.message)
            + '</div>';
        return;
    }
    summary.innerHTML = '<div class="text-muted">Активных звонков и ожидающих анкет сейчас нет.</div>';
}

function renderWorkspaceCalls(calls) {
    var tbody = document.getElementById('workspace_recent_calls');
    if (!tbody) return;
    if (!Array.isArray(calls) || calls.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-3">Звонков пока нет.</td></tr>';
        return;
    }
    tbody.innerHTML = calls.map(function (call) {
        var surveyBadge = call.has_survey
            ? '<span class="ph-badge is-success"><i class="fas fa-check"></i> есть</span>'
            : '<span class="ph-badge is-warning"><i class="fas fa-exclamation"></i> нет</span>';
        return '<tr>'
            + '<td><small class="text-muted">#' + escapeHtml(call.id) + '</small></td>'
            + '<td><a href="' + escapeHtml(call.report_url) + '">' + escapeHtml(call.phone) + '</a></td>'
            + '<td><small>' + escapeHtml(call.created_at) + '</small></td>'
            + '<td><small>' + escapeHtml(call.dialog_duration_human) + '</small></td>'
            + '<td>' + surveyBadge + '</td>'
            + '</tr>';
    }).join('');
}

function renderWorkspaceSurveys(surveys) {
    var tbody = document.getElementById('workspace_recent_surveys');
    if (!tbody) return;
    if (!Array.isArray(surveys) || surveys.length === 0) {
        tbody.innerHTML = '<tr><td colspan="3" class="text-center text-muted py-3">Анкет пока нет.</td></tr>';
        return;
    }
    tbody.innerHTML = surveys.map(function (survey) {
        return '<tr>'
            + '<td><a href="' + escapeHtml(survey.report_url) + '">#' + escapeHtml(survey.call_id) + '</a></td>'
            + '<td><small>' + escapeHtml(survey.reason_label || 'Без причины') + '</small></td>'
            + '<td><small>' + escapeHtml(survey.status_label || '-') + '</small></td>'
            + '</tr>';
    }).join('');
}

function renderWorkspaceStats(data) {
    var stats = data.stats || {};
    var setText = function (id, value) {
        var element = document.getElementById(id);
        if (element) element.textContent = value == null ? '' : value;
    };
    setText('stat_total_calls', stats.today_total_calls || 0);
    setText('stat_surveyed_calls', stats.today_surveyed_calls || 0);
    setText('stat_pending_surveys', stats.today_pending_surveys || 0);
    setText('stat_avg_talk', stats.today_avg_talk_human || '0:00');
    setText('stat_inbound', stats.today_inbound_calls || 0);
    setText('stat_outbound', stats.today_outbound_calls || 0);
    setText('stat_talked', stats.today_talked_calls || 0);
    setText('workspace_generated_at', data.generated_at || '');

    renderWorkspaceSummary(data);
    renderWorkspaceCalls(data.recent_calls || []);
    renderWorkspaceSurveys(data.recent_surveys || []);
}

document.addEventListener('DOMContentLoaded', function () {
    var initialNode = document.getElementById('operatorWorkspaceInitialData');
    if (initialNode) {
        try {
            renderWorkspaceStats(JSON.parse(initialNode.textContent || '{}'));
        } catch (error) { console.error(error); }
    }
    window.addEventListener('operator-workspace:update', function (event) {
        renderWorkspaceStats(event.detail || {});
    });
});
</script>
@endsection
