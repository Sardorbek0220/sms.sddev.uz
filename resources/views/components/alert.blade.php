{{--
    Alert — flash messages, errors, info.
    Auto-renders if session has 'success' / 'error' / 'warning' when used as <x-alert :flash="true"/>.

    Usage:
      <x-alert type="success">Сохранено</x-alert>
      <x-alert type="danger" :dismissible="true">Ошибка</x-alert>
      <x-alert :flash="true"/>     {{-- prints session('success'/'error') automatically --}}

    Props:
      type         string — success | warning | danger | info (default info)
      dismissible  bool   — show close button (default false)
      icon         string — Font Awesome class (auto-picked from type)
      flash        bool   — render session flashes if present (success / error / warning)
--}}
@php
    $type = $type ?? 'info';
    $allowed = ['success', 'warning', 'danger', 'info'];
    if (!in_array($type, $allowed, true)) $type = 'info';
    $dismissible = isset($dismissible) ? (bool)$dismissible : false;
    $icons = [
        'success' => 'fa-check-circle',
        'warning' => 'fa-exclamation-triangle',
        'danger'  => 'fa-times-circle',
        'info'    => 'fa-info-circle',
    ];
    $iconClass = isset($icon) && $icon
        ? (strpos($icon, 'fa-') === 0 ? $icon : 'fa-' . $icon)
        : $icons[$type];
    $flash = isset($flash) ? (bool)$flash : false;
@endphp

@if($flash)
    @foreach(['success' => 'success', 'error' => 'danger', 'warning' => 'warning', 'info' => 'info'] as $key => $cssType)
        @if(session()->has($key))
            <div class="alert alert-{{ $cssType }} d-flex align-items-start" role="alert" style="border-radius:12px;">
                <i class="fas {{ $icons[$cssType] }} mt-1 mr-2"></i>
                <div class="flex-grow-1">{{ session($key) }}</div>
                <button type="button" class="close ml-2" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
        @endif
    @endforeach
@else
    <div {{ $attributes->merge(['class' => 'alert alert-' . $type . ($dismissible ? ' alert-dismissible' : '') . ' d-flex align-items-start']) }}
         role="alert" style="border-radius:12px;">
        <i class="fas {{ $iconClass }} mt-1 mr-2"></i>
        <div class="flex-grow-1">{{ $slot }}</div>
        @if($dismissible)
            <button type="button" class="close ml-2" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        @endif
    </div>
@endif
