@extends('admin.layouts.index')

@section('content')
<div class="content-wrapper">
  <section class="content-header">
    <div class="container-fluid">
      <h1 class="m-0">{{ __('Рабочие часы по дням и компаниям') }}</h1>
      <small class="text-muted">Если для конкретной компании ничего не настроено — используется блок «По умолчанию».</small>
    </div>
  </section>

  <section class="content">
    <div class="container-fluid">
      @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
      @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

      <form method="POST" action="{{ route('admin.work-hours.update') }}">
        @csrf

        @foreach($gateways as $gw => $label)
          @php $rows = $fullMap[$gw] ?? []; @endphp
          <div class="card mb-3">
            <div class="card-header" style="background:{{ $gw === '' ? '#f1f5f9' : '#fff' }};">
              <h5 class="m-0" style="font-weight:600;">{{ $label }}</h5>
            </div>
            <div class="card-body" style="overflow-x:auto;">
              <table class="table table-bordered align-middle mb-0" style="max-width: 760px; font-size: 0.92rem;">
                <thead style="background:#f8fafc;">
                  <tr>
                    <th style="width: 32%;">{{ __('День') }}</th>
                    <th class="text-center" style="width: 14%;">{{ __('Активен') }}</th>
                    <th class="text-center" style="width: 27%;">{{ __('Начало') }}</th>
                    <th class="text-center" style="width: 27%;">{{ __('Конец') }}</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($weekdayLabels as $weekday => $dayName)
                    @php $r = $rows[$weekday] ?? ['start_hour' => 9, 'end_hour' => 18, 'is_active' => true]; @endphp
                    <tr>
                      <td><strong>{{ $dayName }}</strong></td>
                      <td class="text-center">
                        <input type="hidden" name="hours[{{ $gw }}][{{ $weekday }}][is_active]" value="0">
                        <input type="checkbox" name="hours[{{ $gw }}][{{ $weekday }}][is_active]" value="1"
                               {{ ($r['is_active'] ?? false) ? 'checked' : '' }}>
                      </td>
                      <td class="text-center">
                        <select name="hours[{{ $gw }}][{{ $weekday }}][start_hour]" class="form-control" style="display:inline-block;width:auto;">
                          @for($h = 0; $h <= 23; $h++)
                            <option value="{{ $h }}" {{ (int)$r['start_hour'] === $h ? 'selected' : '' }}>{{ sprintf('%02d:00', $h) }}</option>
                          @endfor
                        </select>
                      </td>
                      <td class="text-center">
                        <select name="hours[{{ $gw }}][{{ $weekday }}][end_hour]" class="form-control" style="display:inline-block;width:auto;">
                          @for($h = 1; $h <= 24; $h++)
                            <option value="{{ $h }}" {{ (int)$r['end_hour'] === $h ? 'selected' : '' }}>{{ sprintf('%02d:00', $h) }}</option>
                          @endfor
                        </select>
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>
        @endforeach

        <div class="mb-4">
          <button type="submit" class="btn btn-primary">{{ __('Сохранить всё') }}</button>
          <a href="{{ route('admin.monitoring') }}" class="btn btn-default">{{ __('К мониторингу') }}</a>
        </div>
      </form>
    </div>
  </section>
</div>
@endsection
