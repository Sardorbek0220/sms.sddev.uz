@php
    $isOperator = isset($isOperator) ? $isOperator : false;
    $cnaRoute       = $isOperator ? 'operator.calls-no-anketa'        : 'admin.calls-no-anketa';
    $cnaAnketiLink  = $isOperator ? 'operator.workspace'              : 'admin.anketi';
    $cnaCallsCrumb  = $isOperator ? 'operator.report.calls'           : 'admin.report.calls';
    $cnaDashCrumb   = $isOperator ? 'operator.workspace'              : 'admin.dashboard';
    $cnaSurveyRoute = $isOperator ? 'operator.call-surveys.create'    : 'admin.call-surveys.create';
@endphp
@extends('admin.layouts.index')

@php
use App\Http\Controllers\Admin\CallsWithoutSurveyController;
if (!function_exists('ank_initials_v2')) {
    function ank_initials_v2($name) {
        $parts = preg_split('/[\s_]+/u', trim((string)$name));
        $parts = array_values(array_filter($parts));
        if (count($parts) === 0) return '?';
        if (count($parts) === 1) return mb_strtoupper(mb_substr($parts[0], 0, 2));
        return mb_strtoupper(mb_substr($parts[0], 0, 1) . mb_substr($parts[count($parts)-1], 0, 1));
    }
}
if (!function_exists('ank_color_v2')) {
    function ank_color_v2($seed) {
        $palette = ['#5b67f4', '#0ea5e9', '#10b981', '#f59e0b', '#ef4444', '#a855f7', '#ec4899', '#14b8a6', '#84cc16', '#06b6d4'];
        $h = abs(crc32((string)$seed));
        return $palette[$h % count($palette)];
    }
}
@endphp

@section('content')
<div class="content-wrapper">
<div class="ds-root">
<div class="ds-shell">
<div class="ds-container">

    <nav class="ds-crumbs">
        <a href="{{ route($cnaDashCrumb) }}">Главная</a>
        <span class="ds-crumb-sep">/</span>
        <a href="{{ route($cnaCallsCrumb) }}">Звонки</a>
        <span class="ds-crumb-sep">/</span>
        <span class="ds-crumb-current">Без анкеты</span>
    </nav>

    <header class="ds-page-header">
        <div>
            <h1 class="ds-page-title">Звонки без анкеты</h1>
            <div class="ds-page-sub">
                {{ \Carbon\Carbon::parse($from_date)->isoFormat('D MMMM Y') }} —
                {{ \Carbon\Carbon::parse($to_date)->isoFormat('D MMMM Y') }} ·
                {{ $days }} {{ $days == 1 ? 'день' : ($days < 5 ? 'дня' : 'дней') }}
            </div>
        </div>
        <a href="{{ route($cnaAnketiLink) }}" class="ds-btn">
            <i class="far fa-clipboard"></i> Заполненные анкеты
        </a>
    </header>

    {{-- KPI ── --}}
    <div class="ds-stat-grid">
        <div class="ds-stat">
            <div class="ds-stat-icon ds-stat-icon-warn"><i class="fas fa-exclamation-circle"></i></div>
            <div class="ds-stat-label">Звонков без анкеты</div>
            <div class="ds-stat-value">{{ number_format($total, 0, '.', ' ') }}</div>
        </div>
        <div class="ds-stat">
            <div class="ds-stat-icon ds-stat-icon-blue"><i class="fas fa-phone"></i></div>
            <div class="ds-stat-label">Из них с разговором</div>
            <div class="ds-stat-value">
                {{ number_format($withDialog, 0, '.', ' ') }}
                <span class="ds-stat-value-sub">{{ $total > 0 ? round($withDialog / $total * 100) : 0 }}%</span>
            </div>
        </div>
        <div class="ds-stat">
            <div class="ds-stat-icon ds-stat-icon-info"><i class="fas fa-comments"></i></div>
            <div class="ds-stat-label">С разговором</div>
            <div class="ds-stat-value">{{ number_format($withDialog, 0, '.', ' ') }}</div>
        </div>
        <div class="ds-stat">
            <div class="ds-stat-icon ds-stat-icon-green"><i class="fas fa-check-circle"></i></div>
            <div class="ds-stat-label">Покрытие анкетами</div>
            <div class="ds-stat-value">{{ $coveragePct }}<span class="ds-stat-value-sub">%</span></div>
        </div>
    </div>

    {{-- Filters ── --}}
    <div class="ds-card ds-mt-4">
        <div class="ds-card-pad">
            <form id="cna-filter" action="{{ route($cnaRoute) }}" method="get">

                @php
                    $presets = [
                        ['Сегодня',  date('Y-m-d'), date('Y-m-d')],
                        ['Вчера',    date('Y-m-d', strtotime('-1 day')), date('Y-m-d', strtotime('-1 day'))],
                        ['Неделя',   date('Y-m-d', strtotime('-7 days')), date('Y-m-d')],
                        ['Месяц',    date('Y-m-d', strtotime('-30 days')), date('Y-m-d')],
                    ];
                @endphp
                <div class="ds-flex-between" style="gap:var(--s-3); flex-wrap:wrap;">
                    <div class="ds-segment">
                        @foreach($presets as [$lbl, $f, $t])
                            <a href="{{ route($cnaRoute, ['from_date'=>$f, 'to_date'=>$t]) }}"
                               class="{{ $from_date === $f && $to_date === $t ? 'is-active' : '' }}">{{ $lbl }}</a>
                        @endforeach
                    </div>
                    <span class="ds-text-sm ds-text-muted">
                        <i class="far fa-clock"></i>
                        {{ \Carbon\Carbon::parse($from_date)->format('d.m') }} — {{ \Carbon\Carbon::parse($to_date)->format('d.m') }}
                    </span>
                </div>

                <div class="ds-form-grid ds-mt-4">
                    <div class="ds-field"><label class="ds-label">От</label>
                        <input type="date" name="from_date" value="{{ $from_date }}" class="ds-input">
                    </div>
                    <div class="ds-field"><label class="ds-label">До</label>
                        <input type="date" name="to_date" value="{{ $to_date }}" class="ds-input">
                    </div>
                    @if(!$isOperator)
                    <div class="ds-field"><label class="ds-label">Оператор</label>
                        <select name="operator_id" class="ds-select">
                            <option value="">Все</option>
                            @foreach($operatorOptions as $id => $name)
                                <option value="{{ $id }}" @selected((string)$operator === (string)$id)>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                    <div class="ds-field"><label class="ds-label">Канал</label>
                        <select name="gateway" class="ds-select">
                            <option value="">Все</option>
                            @foreach(\App\Http\Controllers\Admin\CallsWithoutSurveyController::GATEWAYS as $gw => $lbl)
                                <option value="{{ $gw }}" @selected($gateway === $gw)>{{ $lbl }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="ds-field"><label class="ds-label">Направление</label>
                        <select name="direction" class="ds-select">
                            <option value="">Все</option>
                            <option value="inbound"  @selected($direction === 'inbound')>Входящие</option>
                            <option value="outbound" @selected($direction === 'outbound')>Исходящие</option>
                        </select>
                    </div>
                    <!-- type-filter removed -->
                    <div class="ds-field"><label class="ds-label">Мин. разговор (сек)</label>
                        <input type="number" min="0" name="min_dialog" value="{{ $minDialog ?: '' }}" placeholder="0" class="ds-input">
                    </div>
                    <div class="ds-field"><label class="ds-label">Телефон</label>
                        <div class="ds-search">
                            <input type="search" name="phone" value="{{ $phone }}" placeholder="998…" class="ds-input">
                        </div>
                    </div>
                </div>

                <div class="ds-actions">
                    <a href="{{ route($cnaRoute) }}" class="ds-btn ds-btn-ghost">Сбросить</a>
                    <button type="submit" class="ds-btn ds-btn-primary" id="cna-apply"><i class="fas fa-filter"></i> Применить</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Per-operator quick view ── --}}
    @if(!$isOperator && count($byOperator) > 0)
    <div class="ds-card ds-mt-4">
        <div class="ds-card-head">
            <h3>Кто чаще всего не заполняет <span class="ds-count-tag">{{ count($byOperator) }}</span></h3>
            <span class="ds-text-sm ds-text-muted">только звонки за выбранный период</span>
        </div>
        <div class="ds-card-pad">
            <div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: var(--s-3);">
                @foreach($byOperator as $r)
                    @php
                        $name = $opNames[$r->operator_id] ?? ('op#'.$r->operator_id);
                    @endphp
                    <a href="{{ route($cnaRoute, array_merge(request()->query(), ['operator_id' => $r->operator_id])) }}"
                       style="display:flex; align-items:center; gap: var(--s-3); padding: var(--s-3); border:1px solid var(--c-line); border-radius: var(--r-md); background: var(--c-card); transition: all var(--tr); text-decoration:none; color:var(--c-ink);"
                       onmouseover="this.style.borderColor='var(--c-primary)';this.style.boxShadow='var(--sh-md)'"
                       onmouseout="this.style.borderColor='var(--c-line)';this.style.boxShadow=''">
                        <span class="ds-avatar" style="background: {{ ank_color_v2($name) }}; width:36px; height:36px;">{{ ank_initials_v2($name) }}</span>
                        <div style="flex:1; min-width:0;">
                            <div style="font-weight:500; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ $name }}</div>
                            <div class="ds-text-sm ds-text-muted">
                                {{ $r->c }} без анкеты · {{ $r->c_dialog }} с разговором
                            </div>
                        </div>
                        <i class="fas fa-chevron-right ds-text-faint"></i>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    {{-- Results ── --}}
    <div class="ds-card ds-mt-4">
        <div class="ds-card-head">
            <h3>Список звонков <span class="ds-count-tag">{{ $items->total() }}</span></h3>
            <span class="ds-text-sm ds-text-muted">страница {{ $items->currentPage() }} из {{ $items->lastPage() }}</span>
        </div>

        @if($items->isEmpty())
            <div class="ds-empty">
                <div class="ds-empty-icon" style="background: var(--c-success-soft); color: var(--c-success);"><i class="fas fa-check-circle"></i></div>
                <h4>Все звонки покрыты анкетами 🎉</h4>
                <p>За выбранный период не нашлось ни одного незаполненного звонка.</p>
            </div>
        @else
        <div class="ds-table-wrap">
            <table class="ds-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Дата</th>
                        <th>Компания</th>
                        <th>Клиент</th>
                        @if(!$isOperator)<th>Оператор</th>@endif
                        <th>↔</th>
                        <th>Длит.</th>
                        <th style="min-width: 280px;">Аудио</th>
                        <th style="text-align:right; min-width: 140px;">Действие</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $r)
                        @php
                            $opName  = $opNames[$r->operator_id] ?? ($r->operator_id ? ('op#'.$r->operator_id) : '');
                            $gw      = \App\Http\Controllers\Admin\CallsWithoutSurveyController::GATEWAYS[$r->gateway] ?? $r->gateway;
                            $dur     = (int) ($r->dialog_duration ?? 0);
                            $dialogStr = $dur > 0 ? sprintf('%d:%02d', intdiv($dur, 60), $dur % 60) : '0:00';
                            $totalStr  = sprintf('%d:%02d', intdiv($r->call_duration ?? 0, 60), ($r->call_duration ?? 0) % 60);
                            $hasAudio  = !empty($r->recording_local_path) || !empty($r->pbx_audio_url) || !empty($r->telegram_audio_url);
                            $audioRoute = (auth()->user() && method_exists(auth()->user(), 'isOperator') && auth()->user()->isOperator())
                                ? 'operator.calls.audio' : 'admin.calls.audio';
                        @endphp
                        <tr>
                            <td><span class="ds-text-sm ds-text-faint">#{{ $r->id }}</span></td>
                            <td class="ds-num">{{ \Carbon\Carbon::parse($r->created_at)->format('d.m H:i') }}</td>
                            <td>
                                @if(in_array($r->gateway, array_keys(\App\Http\Controllers\Admin\CallsWithoutSurveyController::GATEWAYS)))
                                    <span class="ds-pill ds-pill-info">{{ $gw }}</span>
                                @else
                                    <span class="ds-text-sm ds-text-faint">{{ $r->gateway ?: '—' }}</span>
                                @endif
                            </td>
                            <td class="ds-mono">{{ $r->client_telephone ?: '—' }}</td>
                            @if(!$isOperator)
                            <td>
                                @if($opName !== '')
                                    <span class="ds-op">
                                        <span class="ds-avatar" style="background: {{ ank_color_v2($opName) }};">{{ ank_initials_v2($opName) }}</span>
                                        <span class="ds-op-name">{{ $opName }}</span>
                                    </span>
                                @else
                                    <span class="ds-mute">—</span>
                                @endif
                            </td>
                            @endif
                            <td>
                                @if($r->direction === 'inbound')
                                    <span class="ds-badge ds-badge-info"><i class="fas fa-arrow-down" style="margin-right:2px;"></i> Вх.</span>
                                @elseif($r->direction === 'outbound')
                                    <span class="ds-badge" style="background:var(--c-primary-soft);color:var(--c-primary);"><i class="fas fa-arrow-up" style="margin-right:2px;"></i> Исх.</span>
                                @else
                                    <span class="ds-mute">—</span>
                                @endif
                            </td>
                            <td class="ds-num">{{ $dialogStr }} <span class="ds-text-faint">/ {{ $totalStr }}</span></td>
                            <td>
                                @if($hasAudio)
                                    <audio controls preload="metadata"
                                           style="height:30px; max-width:280px; vertical-align:middle; border-radius:8px;">
                                        @if(!empty($r->recording_local_path) || !empty($r->pbx_audio_url))
                                            <source src="{{ route($audioRoute, $r->id) }}" type="audio/mpeg">
                                        @elseif(!empty($r->telegram_audio_url))
                                            <source src="{{ $r->telegram_audio_url }}" type="audio/mpeg">
                                        @endif
                                        Ваш браузер не поддерживает аудио.
                                    </audio>
                                    @if(empty($r->recording_local_path) && !empty($r->pbx_audio_url))
                                        <small class="ds-text-faint d-block" style="font-size:11px;">⏳ Загрузка с PBX</small>
                                    @endif
                                @else
                                    <span class="ds-mute">—</span>
                                @endif
                            </td>
                            <td style="text-align:right;">
                                <a href="{{ route($cnaSurveyRoute, ['call' => $r->id, 'back' => request()->fullUrl()]) }}"
                                   class="ds-btn ds-btn-sm ds-btn-primary"
                                   target="_blank">
                                    <i class="fas fa-plus"></i> Заполнить
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        @if($items->hasPages())
            <div class="ds-pagination">
                <span class="ds-page-info">{{ $items->firstItem() }}–{{ $items->lastItem() }} из {{ $items->total() }}</span>
                {{ $items->links() }}
            </div>
        @endif
    </div>

</div>
</div>
</div>
</div>

<script>
(function(){
    var form = document.getElementById('cna-filter');
    var applyBtn = document.getElementById('cna-apply');
    if (!form) return;
    form.addEventListener('submit', function(){
        Array.from(form.elements).forEach(function(el){
            if (el.name && el.value === '') el.disabled = true;
        });
        if (applyBtn) {
            applyBtn.classList.add('ds-loading');
            applyBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Загрузка…';
        }
    });
})();
</script>
@endsection
