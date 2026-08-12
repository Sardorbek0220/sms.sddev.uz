@extends('admin.layouts.index')

@php
if (!function_exists('ank_status_class')) {
    function ank_status_class($s) {
        $sl = mb_strtolower((string)$s);
        if (str_contains($sl, 'решено')) return 'ds-badge-success';
        if (str_contains($sl, 'в работе')) return 'ds-badge-warn';
        if (str_contains($sl, 'сброс'))    return 'ds-badge-danger';
        if (str_contains($sl, 'пропущ'))   return 'ds-badge-danger';
        if (str_contains($sl, 'ожидан'))   return 'ds-badge-info';
        if (str_contains($sl, 'переве'))   return 'ds-badge-info';
        if (str_contains($sl, 'уточ'))     return 'ds-badge-info';
        return 'ds-badge-muted';
    }
}
if (!function_exists('ank_initials')) {
    function ank_initials($name) {
        $parts = preg_split('/\s+/u', trim((string)$name));
        $parts = array_values(array_filter($parts));
        if (count($parts) === 0) return '?';
        if (count($parts) === 1) return mb_strtoupper(mb_substr($parts[0], 0, 2));
        return mb_strtoupper(mb_substr($parts[0], 0, 1) . mb_substr($parts[count($parts)-1], 0, 1));
    }
}
if (!function_exists('ank_color')) {
    function ank_color($seed) {
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

    {{-- Breadcrumbs --}}
    <nav class="ds-crumbs" aria-label="breadcrumb">
        <a href="{{ route('admin.dashboard') }}">Главная</a>
        <span class="ds-crumb-sep">/</span>
        <a href="{{ route('admin.report.calls') }}">Звонки</a>
        <span class="ds-crumb-sep">/</span>
        <span class="ds-crumb-current">Анкеты</span>
    </nav>

    {{-- Page header --}}
    <header class="ds-page-header">
        <div>
            <h1 class="ds-page-title">Анкеты</h1>
            <div class="ds-page-sub">
                {{ \Carbon\Carbon::parse($from_date)->isoFormat('D MMMM Y') }}
                — {{ \Carbon\Carbon::parse($to_date)->isoFormat('D MMMM Y') }}
                · {{ $days }} {{ $days == 1 ? 'день' : ($days < 5 ? 'дня' : 'дней') }}
            </div>
        </div>
        <div class="ds-flex ds-gap-2">
            <a href="{{ route('admin.anketi', array_merge(request()->query(), ['export' => 'xlsx'])) }}" class="ds-btn ds-btn-primary">
                <i class="far fa-file-excel"></i> Excel
            </a>
            <a href="{{ route('admin.anketi', array_merge(request()->query(), ['export' => 'csv'])) }}" class="ds-btn">
                <i class="fas fa-download"></i> CSV
            </a>
        </div>
    </header>

    {{-- Stats --}}
    <div class="ds-stat-grid">
        <div class="ds-stat">
            <div class="ds-stat-icon ds-stat-icon-blue"><i class="far fa-clipboard"></i></div>
            <div class="ds-stat-label">Всего анкет</div>
            <div class="ds-stat-value">{{ number_format($total, 0, '.', ' ') }}</div>
        </div>
        <div class="ds-stat">
            <div class="ds-stat-icon ds-stat-icon-green"><i class="far fa-comment-dots"></i></div>
            <div class="ds-stat-label">С комментарием</div>
            <div class="ds-stat-value">
                {{ number_format($withComment, 0, '.', ' ') }}
                <span class="ds-stat-value-sub">{{ $total > 0 ? round($withComment / $total * 100) : 0 }}%</span>
            </div>
        </div>
        <div class="ds-stat">
            <div class="ds-stat-icon ds-stat-icon-warn"><i class="fas fa-chart-line"></i></div>
            <div class="ds-stat-label">В среднем в день</div>
            <div class="ds-stat-value">{{ $avgPerDay }}</div>
        </div>
        <div class="ds-stat">
            <div class="ds-stat-icon ds-stat-icon-info"><i class="far fa-calendar"></i></div>
            <div class="ds-stat-label">Дней в выборке</div>
            <div class="ds-stat-value">{{ $days }}</div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="ds-card ds-mt-4">
        <div class="ds-card-pad">
            <form id="ank-filter" action="{{ route('admin.anketi') }}" method="get">

                @php
                    $presets = [
                        ['Сегодня',  date('Y-m-d'), date('Y-m-d')],
                        ['Вчера',    date('Y-m-d', strtotime('-1 day')), date('Y-m-d', strtotime('-1 day'))],
                        ['Неделя',   date('Y-m-d', strtotime('-7 days')), date('Y-m-d')],
                        ['Месяц',    date('Y-m-d', strtotime('-30 days')), date('Y-m-d')],
                        ['Квартал',  date('Y-m-d', strtotime('-90 days')), date('Y-m-d')],
                    ];
                @endphp
                <div class="ds-flex-between" style="gap:var(--s-3); flex-wrap:wrap;">
                    <div class="ds-segment">
                        @foreach($presets as [$lbl, $f, $t])
                            <a href="{{ route('admin.anketi', ['from_date'=>$f, 'to_date'=>$t]) }}"
                               class="{{ $from_date === $f && $to_date === $t ? 'is-active' : '' }}">{{ $lbl }}</a>
                        @endforeach
                    </div>
                    <span class="ds-text-sm ds-text-muted">
                        <i class="far fa-clock"></i>
                        {{ \Carbon\Carbon::parse($from_date)->format('d.m') }} — {{ \Carbon\Carbon::parse($to_date)->format('d.m') }}
                    </span>
                </div>

                <div class="ds-form-grid ds-mt-4">
                    <div class="ds-field">
                        <label class="ds-label">От</label>
                        <input type="date" name="from_date" value="{{ $from_date }}" class="ds-input">
                    </div>
                    <div class="ds-field">
                        <label class="ds-label">До</label>
                        <input type="date" name="to_date" value="{{ $to_date }}" class="ds-input">
                    </div>
                    <div class="ds-field">
                        <label class="ds-label">Отдел</label>
                        <select name="department" class="ds-select">
                            <option value="">Все</option>
                            @foreach($departmentOptions ?? [] as $d)
                                <option value="{{ $d }}" @selected($department === $d)>{{ $d }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="ds-field">
                        <label class="ds-label">Оператор</label>
                        <select name="operator" class="ds-select">
                            <option value="">Все</option>
                            @foreach($operatorOptions ?? [] as $op)
                                <option value="{{ $op }}" @selected($operator === $op)>{{ $op }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="ds-field">
                        <label class="ds-label">Причина</label>
                        <select name="reason_key" class="ds-select">
                            <option value="">Все</option>
                            @foreach($reasonOptions ?? [] as $opt)
                                <option value="{{ $opt->k }}" @selected($reason === $opt->k)>{{ $opt->l }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="ds-field">
                        <label class="ds-label">Статус</label>
                        <select name="status_label" class="ds-select">
                            <option value="">Все</option>
                            @foreach($statusOptions ?? [] as $opt)
                                <option value="{{ $opt }}" @selected($status === $opt)>{{ $opt }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="ds-field">
                        <label class="ds-label">Телефон</label>
                        <div class="ds-search">
                            <input type="search" name="phone" value="{{ $phone }}" placeholder="998…" class="ds-input">
                        </div>
                    </div>
                    <div class="ds-field">
                        <label class="ds-label">Модуль</label>
                        <div class="ds-search">
                            <input type="search" name="module" value="{{ $module }}" placeholder="Касса, Склад…" class="ds-input">
                        </div>
                    </div>

                    <div class="ds-field">
                        <label class="ds-label">Комментарий</label>
                        <select name="has_comment" class="ds-select">
                            <option value="">Любой</option>
                            <option value="yes" @selected($hasCmt === 'yes')>Есть</option>
                            <option value="no" @selected($hasCmt === 'no')>Нет</option>
                        </select>
                    </div>
                </div>

                <div class="ds-actions">
                    <a href="{{ route('admin.anketi') }}" class="ds-btn ds-btn-ghost">Сбросить</a>
                    <button type="submit" class="ds-btn ds-btn-primary" id="ank-apply">
                        <i class="fas fa-filter"></i> Применить
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Results --}}
    <div class="ds-card ds-mt-4">
        <div class="ds-card-head">
            <h3>Список анкет <span class="ds-count-tag">{{ $items->total() }}</span></h3>
            <span class="ds-text-sm ds-text-muted">страница {{ $items->currentPage() }} из {{ $items->lastPage() }}</span>
        </div>

        @if($items->isEmpty())
            <div class="ds-empty">
                <div class="ds-empty-icon"><i class="far fa-folder-open"></i></div>
                <h4>Ничего не найдено</h4>
                <p>Попробуйте сменить период или сбросить фильтры.</p>
            </div>
        @else
        <div class="ds-table-wrap">
            <table class="ds-table">
                <thead>
                    <tr>
                        <th>Дата</th>
                        <th>Телефон</th>
                        <th>Отдел</th>
                        <th>Оператор</th>
                        <th>Причина</th>
                        <th>Статус</th>
                        <th>Модули</th>
                        <th>Комментарий</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $r)
                        @php
                            $deptArr  = !empty($r->actor_department_names_json) ? json_decode($r->actor_department_names_json, true) : null;
                            $deptList = is_array($deptArr) ? array_values(array_filter(array_map('trim', $deptArr))) : [];
                            if (empty($deptList) && !empty($r->actor_user_id) && isset($opDeptMap[(int) $r->actor_user_id])) {
                                $deptList = $opDeptMap[(int) $r->actor_user_id];
                            }
                            $modArr  = !empty($r->modules_json) ? json_decode($r->modules_json, true) : null;
                            $modList = is_array($modArr) ? array_values(array_filter(array_map('trim', $modArr))) : [];
                            $opName  = (string) ($r->actor_user_name ?? '');
                        @endphp
                        <tr>
                            <td class="ds-num">{{ \Carbon\Carbon::parse($r->created_at)->format('d.m.y · H:i') }}</td>
                            <td class="ds-mono">{{ $r->phone_number ?: '—' }}</td>
                            <td>
                                @forelse($deptList as $d)
                                    <span class="ds-pill ds-pill-info">{{ $d }}</span>
                                @empty
                                    <span class="ds-mute">—</span>
                                @endforelse
                            </td>
                            <td>
                                @if($opName !== '')
                                    <span class="ds-op">
                                        <span class="ds-avatar" style="background: {{ ank_color($opName) }}">{{ ank_initials($opName) }}</span>
                                        <span class="ds-op-name">{{ $opName }}</span>
                                    </span>
                                @else
                                    <span class="ds-mute">—</span>
                                @endif
                            </td>
                            <td>{{ $r->reason_label ?: '—' }}</td>
                            <td>
                                @if($r->status_label)
                                    <span class="ds-badge {{ ank_status_class($r->status_label) }}">{{ $r->status_label }}</span>
                                @else
                                    <span class="ds-mute">—</span>
                                @endif
                            </td>
                            <td>
                                @forelse($modList as $m)
                                    <span class="ds-pill ds-pill-mute">{{ $m }}</span>
                                @empty
                                    <span class="ds-mute">—</span>
                                @endforelse
                            </td>
                            <td>
                                @if(!empty($r->comment_text))
                                    <span class="ds-cmt" title="{{ $r->comment_text }}">{{ $r->comment_text }}</span>
                                @else
                                    <span class="ds-mute">—</span>
                                @endif
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
(function() {
    var form = document.getElementById('ank-filter');
    var applyBtn = document.getElementById('ank-apply');
    if (!form) return;
    form.addEventListener('submit', function() {
        // strip empty fields from URL
        Array.from(form.elements).forEach(function(el){
            if (el.name && el.value === '') el.disabled = true;
        });
        // loading state
        if (applyBtn) {
            applyBtn.classList.add('ds-loading');
            applyBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Загрузка…';
        }
    });
})();
</script>
@endsection
