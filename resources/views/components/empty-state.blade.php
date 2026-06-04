{{--
    Empty state — friendly placeholder for empty tables / lists / filters.

    Usage:
      <x-empty-state />
      <x-empty-state title="Нет звонков" description="За выбранный период звонков не было."/>
      <x-empty-state icon="fa-search" title="Ничего не найдено" description="Сбросьте фильтры или измените период.">
          <x-slot name="action">
              <a href="{{ route('admin.report.calls') }}" class="btn btn-primary btn-sm">Сбросить</a>
          </x-slot>
      </x-empty-state>

    Props:
      icon         string — Font Awesome class (default fa-inbox)
      title        string — heading
      description  string — small line under heading
--}}
@php
    $icon = $icon ?? 'fa-inbox';
    $iconClass = strpos($icon, 'fa-') === 0 ? $icon : 'fa-' . $icon;
    $title = $title ?? 'Нет данных';
    $description = $description ?? '';
@endphp

<div {{ $attributes->merge(['class' => 'ph-empty']) }}>
    <div class="ph-empty-icon"><i class="fas {{ $iconClass }}"></i></div>
    <h4>{{ $title }}</h4>
    @if($description !== '')
        <div class="text-muted small">{{ $description }}</div>
    @endif

    @isset($action)
        <div class="mt-3">{{ $action }}</div>
    @endisset
</div>
