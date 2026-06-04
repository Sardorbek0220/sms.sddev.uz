@extends('operator.layouts.index')

@section('content')
<div class="content-wrapper p-3 p-md-4">
  <div class="container-fluid" style="max-width: 760px;">
    <h1 class="m-0 mb-3" style="font-weight: 600;">Настройки</h1>

    @if(session('success'))
      <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
      <div class="card-body">
        <form method="POST" action="{{ route('operator.settings.update') }}">
          @csrf

          <div class="form-group">
            <h5>Виджет анкеты</h5>
            <p class="text-muted small mb-3">
              Окно «Заполнить анкету» в правом нижнем углу появляется автоматически после звонка.
              Если вам так удобнее — отключите его и заполняйте анкеты вручную из списка звонков.
            </p>
            <div class="custom-control custom-switch">
              <input type="hidden" name="live_survey_widget_enabled" value="0">
              <input type="checkbox" class="custom-control-input" id="live_survey_widget_enabled"
                     name="live_survey_widget_enabled" value="1"
                     {{ ($user->live_survey_widget_enabled ?? true) ? 'checked' : '' }}>
              <label class="custom-control-label" for="live_survey_widget_enabled">
                Показывать виджет анкеты
              </label>
            </div>
          </div>

          <hr>

          <button type="submit" class="btn btn-primary">Сохранить</button>
          <a href="{{ route('operator.workspace') }}" class="btn btn-default">Назад</a>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
