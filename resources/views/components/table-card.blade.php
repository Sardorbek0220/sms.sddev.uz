{{--
    Table card — wraps a table in a styled card with optional title and footer.
    Default slot is the <table>. If $empty is true, shows the empty-state instead.

    Usage:
      <x-table-card title="Звонки" :empty="$rows->isEmpty()">
          <table class="table">...</table>
          <x-slot name="footer">
              {{ $rows->links() }}
          </x-slot>
      </x-table-card>

    Props:
      title        string  — card title (optional)
      empty        bool    — when true, render empty-state instead of slot (default false)
      empty-text   string  — empty state title (default "Нет данных")
      empty-hint   string  — empty state hint
--}}
@php
    $title = $title ?? null;
    $empty = isset($empty) ? (bool)$empty : false;
    $emptyText = $emptyText ?? 'Нет данных';
    $emptyHint = $emptyHint ?? 'Попробуйте изменить фильтры или зайти позже.';
@endphp

<div {{ $attributes->merge(['class' => 'card']) }}>
    @if($title || isset($headerRight))
        <div class="card-header d-flex justify-content-between align-items-center">
            @if($title)
                <h3 class="card-title mb-0" style="font-weight:600;font-size:1rem;">{{ $title }}</h3>
            @endif
            @isset($headerRight)
                <div>{{ $headerRight }}</div>
            @endisset
        </div>
    @endif

    <div class="card-body p-0">
        @if($empty)
            <x-empty-state :title="$emptyText" :description="$emptyHint" />
        @else
            <div class="table-responsive">
                {{ $slot }}
            </div>
        @endif
    </div>

    @isset($footer)
        <div class="card-footer bg-transparent">
            {{ $footer }}
        </div>
    @endisset
</div>
