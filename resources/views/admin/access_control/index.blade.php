@extends('admin.layouts.index')

@section('content')
<div class="content-wrapper">
  <section class="content-header">
    <div class="container-fluid">
      <h1 class="m-0">{{ __('Управление доступами') }}</h1>
      <small class="text-muted">Кто из не-операторов какие страницы видит. Админы (role=admin) имеют полный доступ автоматически.</small>
    </div>
  </section>

  <section class="content">
    <div class="container-fluid">
      @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
      @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

      <form method="POST" action="{{ route('admin.access-control.update') }}">
        @csrf
        <div class="card">
          <div class="card-body p-0" style="overflow-x: auto;">
            <table class="table table-bordered mb-0" style="font-size: 0.88rem;">
              <thead style="background: #f8fafc;">
                <tr>
                  <th rowspan="2" style="vertical-align: middle; min-width: 220px; position: sticky; left: 0; background: #f8fafc; z-index: 2;">
                    {{ __('Пользователь') }}
                  </th>
                  @foreach($permissions as $cat => $perms)
                    <th colspan="{{ count($perms) }}" class="text-center" style="background: #eef2f7;">
                      {{ ['main' => 'Главные', 'reports' => 'Отчёты', 'people' => 'Люди', 'settings' => 'Настройки'][$cat] ?? $cat }}
                    </th>
                  @endforeach
                </tr>
                <tr>
                  @foreach($permissions as $perms)
                    @foreach($perms as $p)
                      <th class="text-center" style="writing-mode: vertical-rl; transform: rotate(180deg); white-space: nowrap; padding: 8px 4px; font-weight: 500; min-width: 36px;">
                        {{ $p->label }}
                      </th>
                    @endforeach
                  @endforeach
                </tr>
              </thead>
              <tbody>
                @foreach($users as $u)
                  <tr>
                    <td style="position: sticky; left: 0; background: #fff; z-index: 1;">
                      <strong>{{ $u->name }}</strong>
                      <div class="text-muted small">
                        {{ $u->email }} · <span class="badge badge-{{ $u->role === 'admin' ? 'danger' : 'info' }}">{{ $u->role }}</span>
                      </div>
                    </td>
                    @foreach($permissions as $perms)
                      @foreach($perms as $p)
                        @php $checked = in_array($p->permission_key, $grants[$u->id] ?? [], true) || $u->role === 'admin'; @endphp
                        <td class="text-center" style="vertical-align: middle;">
                          <input type="checkbox"
                                 name="grants[{{ $u->id }}][]"
                                 value="{{ $p->permission_key }}"
                                 {{ $checked ? 'checked' : '' }}
                                 {{ $u->role === 'admin' ? 'disabled title="Полный доступ — admin"' : '' }}>
                        </td>
                      @endforeach
                    @endforeach
                  </tr>
                @endforeach
                @if($users->isEmpty())
                  <tr><td colspan="{{ $permissions->flatten()->count() + 1 }}" class="text-center text-muted py-3">Не-операторов нет.</td></tr>
                @endif
              </tbody>
            </table>
          </div>
          <div class="card-footer">
            <button type="submit" class="btn btn-primary">{{ __('Сохранить') }}</button>
            <a href="{{ route('admin.users.index') }}" class="btn btn-default">{{ __('Назад') }}</a>
            <small class="text-muted ml-3">Админ-пользователи отмечены автоматически и неизменяемы — у них всегда полный доступ.</small>
          </div>
        </div>
      </form>
    </div>
  </section>
</div>
@endsection
