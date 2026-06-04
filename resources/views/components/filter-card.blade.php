{{--
    Filter card — wraps a GET form for table filters.
    Provides reset button and submit. Inputs are passed as default slot.

    Usage:
      <x-filter-card :action="route('admin.report.calls')" :reset-url="route('admin.report.calls')">
          <div>
              <label>Период</label>
              <input type="text" name="date" value="{{ request('date') }}" class="form-control form-control-sm">
          </div>
          <div>
              <label>Компания</label>
              <select name="gateway" class="form-control form-control-sm">...</select>
          </div>
      </x-filter-card>

    Props:
      action     string  — form action URL
      method     string  — GET (default) | POST
      reset-url  string  — URL for "Сбросить" button (default: action)
      submit-label string  — default "Применить"
--}}
@php
    $action = $action ?? url()->current();
    $method = strtoupper($method ?? 'GET');
    $resetUrl = $resetUrl ?? $action;
    $submitLabel = $submitLabel ?? 'Применить';
@endphp

<form action="{{ $action }}" method="{{ $method }}" {{ $attributes->merge(['class' => 'ph-filter-card']) }}>
    @if($method === 'POST') @csrf @endif

    <div class="ph-filter-row">
        {{ $slot }}
    </div>

    <div class="ph-filter-actions">
        <a href="{{ $resetUrl }}" class="btn btn-light btn-sm">
            <i class="fas fa-undo"></i> Сбросить
        </a>
        <button type="submit" class="btn btn-primary btn-sm">
            <i class="fas fa-filter"></i> {{ $submitLabel }}
        </button>
    </div>
</form>
