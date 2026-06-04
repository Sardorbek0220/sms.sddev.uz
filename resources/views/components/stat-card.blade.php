{{--
    Stat card — single number with label, optional hint and icon.

    Usage:
      <x-stat-card label="Звонки сегодня" value="42" />
      <x-stat-card label="Пропущенные" value="5" variant="danger" icon="fa-phone-slash" hint="+2 vs вчера" />
      <x-stat-card label="Avg score" value="4.2" variant="success" icon="fa-star" />

    Props:
      label    string   — top label (uppercase)
      value    string   — main number
      hint     string   — optional small line below value
      variant  string   — primary | success | warning | danger | info | muted (default: primary)
      icon     string   — Font Awesome class without "fa-" prefix or with it (e.g. "fa-phone" or "phone")
--}}
@php
    $variant = $variant ?? 'primary';
    $allowed = ['primary', 'success', 'warning', 'danger', 'info', 'muted'];
    if (!in_array($variant, $allowed, true)) $variant = 'primary';
    $iconClass = isset($icon) && $icon ? (strpos($icon, 'fa-') === 0 ? $icon : 'fa-' . $icon) : null;
@endphp

<div {{ $attributes->merge(['class' => 'ph-stat-card is-' . $variant]) }}>
    @if($iconClass)
        <span class="ph-stat-icon"><i class="fas {{ $iconClass }}"></i></span>
    @endif

    <div class="ph-stat-label">{{ $label ?? '' }}</div>
    <div class="ph-stat-value">{!! $value ?? '—' !!}</div>

    @if(isset($hint) && $hint !== '')
        <div class="ph-stat-hint">{{ $hint }}</div>
    @endif

    {{-- Allow extra content below (e.g., trend sparkline) --}}
    {{ $slot ?? '' }}
</div>
