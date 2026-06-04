@extends('admin.layouts.index')

@section('content')
<div class="content-wrapper">
  <section class="content-header">
    <div class="container-fluid">
      <h1 class="m-0">{{ __('Журнал действий') }}</h1>
      <small class="text-muted">Все админские действия, входы/выходы, изменения настроек.</small>
    </div>
  </section>

  <section class="content">
    <div class="container-fluid">
      <div class="card mb-3">
        <div class="card-body">
          <form method="GET" class="form-inline" style="gap: 10px; flex-wrap: wrap;">
            <input type="text"  name="q"      value="{{ $filters['q'] ?? '' }}"      class="form-control mr-2" placeholder="Поиск (сообщение/путь/имя)" style="min-width: 280px;">
            <select name="action" class="form-control mr-2">
              <option value="">{{ __('Все действия') }}</option>
              @foreach($actions as $a)
                <option value="{{ $a }}" {{ ($filters['action'] ?? '') === $a ? 'selected' : '' }}>{{ $a }}</option>
              @endforeach
            </select>
            <input type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="form-control mr-2">
            <input type="date" name="to"   value="{{ $filters['to']   ?? '' }}" class="form-control mr-2">
            <button class="btn btn-primary" type="submit">{{ __('Фильтр') }}</button>
            <a href="{{ route('admin.audit-log') }}" class="btn btn-outline-secondary ml-2">{{ __('Сбросить') }}</a>
          </form>
        </div>
      </div>

      <div class="card">
        <div class="card-body p-0">
          <table class="table table-striped table-hover mb-0" style="font-size: 0.92rem;">
            <thead style="background: #f8fafc;">
              <tr>
                <th style="width: 150px;">{{ __('Время') }}</th>
                <th style="width: 160px;">{{ __('Пользователь') }}</th>
                <th style="width: 130px;">{{ __('Действие') }}</th>
                <th>{{ __('Описание') }}</th>
                <th style="width: 110px;">{{ __('IP') }}</th>
                <th style="width: 60px;"></th>
              </tr>
            </thead>
            <tbody>
              @forelse($logs as $row)
                <tr>
                  <td>
                    <small>{{ \Carbon\Carbon::parse($row->created_at)->format('d.m H:i:s') }}</small>
                  </td>
                  <td>
                    @if($row->user_name)
                      <strong>{{ $row->user_name }}</strong><br>
                      <small class="text-muted">id={{ $row->user_id }}</small>
                    @else
                      <span class="text-muted">—</span>
                    @endif
                  </td>
                  <td>
                    <span class="badge badge-{{ $row->action === 'login' ? 'success' : ($row->action === 'logout' ? 'secondary' : ($row->action === 'http_delete' ? 'danger' : ($row->action === 'http_post' || $row->action === 'http_put' || $row->action === 'http_patch' ? 'info' : 'light'))) }}">{{ $row->action }}</span>
                  </td>
                  <td>
                    {{ $row->message ?: '—' }}
                    @if($row->entity_type)
                      <br><small class="text-muted">{{ $row->entity_type }}{{ $row->entity_id ? '#'.$row->entity_id : '' }}</small>
                    @endif
                  </td>
                  <td><small>{{ $row->ip ?: '—' }}</small></td>
                  <td>
                    @if(!empty($row->payload))
                      <button type="button" class="btn btn-sm btn-outline-secondary" data-toggle="collapse" data-target="#payload-{{ $row->id }}">▼</button>
                    @endif
                  </td>
                </tr>
                @if(!empty($row->payload))
                  <tr id="payload-{{ $row->id }}" class="collapse">
                    <td colspan="6" style="background: #f8fafc;">
                      <pre style="margin:0; font-size: 11px; white-space: pre-wrap;">{{ json_encode($row->payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) }}</pre>
                    </td>
                  </tr>
                @endif
              @empty
                <tr>
                  <td colspan="6" class="text-center text-muted py-4">{{ __('Записей нет') }}</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
        <div class="card-footer">
          {{ $logs->links() }}
        </div>
      </div>
    </div>
  </section>
</div>
@endsection
