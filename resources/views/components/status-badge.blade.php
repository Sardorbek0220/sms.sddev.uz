{{--
    Status badge — colored pill for call status, score, gateway, etc.

    Usage:
      <x-status-badge status="answered"/>          {{-- green "Отвечен" --}}
      <x-status-badge status="missed"/>            {{-- red "Пропущен" --}}
      <x-status-badge status="outgoing"/>          {{-- blue "Исходящий" --}}
      <x-status-badge status="incoming"/>          {{-- info "Входящий" --}}
      <x-status-badge :score="5"/>                 {{-- green "5" --}}
      <x-status-badge :score="2"/>                 {{-- red "2" --}}
      <x-status-badge variant="warning">SMS не отправлено</x-status-badge>

    Props:
      status   string — well-known key (answered/missed/outgoing/incoming/sms_sent/sms_pending/feedback_received)
      score    int    — 1..5; auto color
      variant  string — primary | success | warning | danger | info | muted (manual override)
      icon     string — Font Awesome class (optional)
      label    string — override label text
--}}
@php
    $variant = $variant ?? null;
    $label = $label ?? null;
    $iconClass = isset($icon) && $icon ? (strpos($icon, 'fa-') === 0 ? $icon : 'fa-' . $icon) : null;

    // Status preset
    if (!empty($status)) {
        $presets = [
            'answered'           => ['variant' => 'success',  'label' => 'Отвечен',         'icon' => 'fa-phone'],
            'missed'             => ['variant' => 'danger',   'label' => 'Пропущен',        'icon' => 'fa-phone-slash'],
            'outgoing'           => ['variant' => 'primary',  'label' => 'Исходящий',       'icon' => 'fa-arrow-up'],
            'incoming'           => ['variant' => 'info',     'label' => 'Входящий',        'icon' => 'fa-arrow-down'],
            'sms_sent'           => ['variant' => 'success',  'label' => 'SMS отправлено',  'icon' => 'fa-comment-dots'],
            'sms_pending'        => ['variant' => 'muted',    'label' => 'SMS не отправлено','icon' => 'fa-comment-slash'],
            'feedback_received'  => ['variant' => 'success',  'label' => 'Анкета получена', 'icon' => 'fa-check-circle'],
            'feedback_pending'   => ['variant' => 'muted',    'label' => 'Без анкеты',      'icon' => 'fa-circle'],
            'online'             => ['variant' => 'success',  'label' => 'Онлайн',          'icon' => 'fa-circle'],
            'offline'            => ['variant' => 'muted',    'label' => 'Офлайн',          'icon' => 'fa-circle'],
            'on_call'            => ['variant' => 'warning',  'label' => 'В разговоре',     'icon' => 'fa-headset'],
            'idle'               => ['variant' => 'info',     'label' => 'Свободен',        'icon' => 'fa-coffee'],
        ];
        if (isset($presets[$status])) {
            $variant = $variant ?? $presets[$status]['variant'];
            $label   = $label   ?? $presets[$status]['label'];
            $iconClass = $iconClass ?? $presets[$status]['icon'];
        }
    }

    // Score auto-color
    if (isset($score) && $score !== null && $score !== '') {
        $sv = (int)$score;
        if (!$variant) {
            if ($sv >= 4)      $variant = 'success';
            elseif ($sv === 3) $variant = 'warning';
            else               $variant = 'danger';
        }
        if (!$label) $label = (string)$sv;
        if (!$iconClass) $iconClass = 'fa-star';
    }

    if (!$variant) $variant = 'muted';
@endphp

<span {{ $attributes->merge(['class' => 'ph-badge is-' . $variant]) }}>
    @if($iconClass)<i class="fas {{ $iconClass }}"></i>@endif
    {{ $label ?? trim((string)($slot ?? '')) }}
</span>
