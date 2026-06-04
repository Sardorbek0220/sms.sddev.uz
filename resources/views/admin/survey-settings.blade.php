@extends('admin.layouts.index')

@section('content')
<div class="content-wrapper p-3 p-md-4">

    <x-page-header title="Настройки анкеты"
                   subtitle="Причины обращения, модули и статусы для формы анкеты звонка"
                   :breadcrumbs="[
                       ['label' => 'Главная', 'url' => route('admin.bigreport')],
                       ['label' => 'Настройки анкеты'],
                   ]"/>

    @if(session('success'))<div class="alert alert-success" style="border-radius:12px;"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger" style="border-radius:12px;"><i class="fas fa-times-circle"></i> {{ session('error') }}</div>@endif

    <div class="alert alert-info" style="border-radius:12px;font-size:.92rem;">
        <i class="fas fa-info-circle"></i>
        Здесь редактируется конфиг для формы анкеты, которую открывают операторы и админы из карточки звонка.
        Каждая <strong>причина обращения</strong> может использовать общий список модулей, тем оплаты или свой собственный.
        Сохраняется в <code>devteam.b24_call_survey_app_settings</code>.
    </div>

    <form method="POST" action="{{ route('admin.survey-settings.update') }}">
        @csrf

        <div class="row">
            <div class="col-lg-4 mb-3">
                <div class="card h-100">
                    <div class="card-header">
                        <h3 class="card-title mb-0" style="font-weight:600;font-size:1rem;">
                            <i class="fas fa-th-large"></i> Общие модули (common)
                        </h3>
                    </div>
                    <div class="card-body">
                        <small class="text-muted d-block mb-2">Один модуль — одна строка. Используется причинами с module_set = common.</small>
                        <textarea name="common_modules" rows="14" class="form-control" style="font-family:monospace;font-size:.9rem;">{{ implode("\n", $config['common_modules']) }}</textarea>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 mb-3">
                <div class="card h-100">
                    <div class="card-header">
                        <h3 class="card-title mb-0" style="font-weight:600;font-size:1rem;">
                            <i class="fas fa-credit-card"></i> Темы оплаты (payment)
                        </h3>
                    </div>
                    <div class="card-body">
                        <small class="text-muted d-block mb-2">Используется причинами с module_set = payment.</small>
                        <textarea name="payment_topics" rows="14" class="form-control" style="font-family:monospace;font-size:.9rem;">{{ implode("\n", $config['payment_topics']) }}</textarea>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 mb-3">
                <div class="card h-100">
                    <div class="card-header">
                        <h3 class="card-title mb-0" style="font-weight:600;font-size:1rem;">
                            <i class="fas fa-tasks"></i> Статусы (unified)
                        </h3>
                    </div>
                    <div class="card-body">
                        <small class="text-muted d-block mb-2">Общие статусы (если у причины нет custom_statuses).</small>
                        <textarea name="unified_statuses" rows="14" class="form-control" style="font-family:monospace;font-size:.9rem;">{{ implode("\n", $config['unified_statuses']) }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title mb-0" style="font-weight:600;font-size:1rem;">
                    <i class="fas fa-list-alt"></i> Причины обращения ({{ count($config['reasons']) }})
                </h3>
                <button type="button" class="btn btn-success btn-sm" onclick="addReason()">
                    <i class="fas fa-plus"></i> Добавить причину
                </button>
            </div>
            <div class="card-body" id="reasons-container">
                @foreach($config['reasons'] as $i => $r)
                    @include('admin._survey_reason_card', ['r' => $r, 'i' => $i])
                @endforeach
            </div>
        </div>

        <div class="d-flex mt-3" style="gap:10px;">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Сохранить настройки</button>
            <a href="{{ route('admin.survey-settings') }}" class="btn btn-light">Отменить</a>
        </div>
    </form>

</div>

<template id="reason-template">
    @include('admin._survey_reason_card', ['r' => ['k'=>'','l'=>'','module_set'=>'common','module_title'=>'Модули','req'=>false,'custom_modules'=>[],'custom_statuses'=>[]], 'i' => '__INDEX__'])
</template>

<script>
    var reasonCount = {{ count($config['reasons']) }};

    function addReason() {
        var tpl = document.getElementById('reason-template').innerHTML.replace(/__INDEX__/g, reasonCount);
        var wrap = document.createElement('div');
        wrap.innerHTML = tpl;
        document.getElementById('reasons-container').appendChild(wrap.firstElementChild);
        reasonCount++;
    }

    function removeReason(btn) {
        if (confirm('Удалить причину?')) btn.closest('.ph-reason-card').remove();
    }

    function toggleCustom(select) {
        var card = select.closest('.ph-reason-card');
        var customBox = card.querySelector('.ph-custom-box');
        if (customBox) customBox.style.display = (select.value === 'custom') ? 'block' : 'none';
    }
</script>
@endsection
