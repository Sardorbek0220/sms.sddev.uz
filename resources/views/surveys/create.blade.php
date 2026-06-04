@extends(auth()->check() && auth()->user()->isOperator() ? 'operator.layouts.index' : 'admin.layouts.index')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Заполнить анкету</h1>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="row">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Информация о звонке</h3>
                        </div>
                        <div class="card-body">
                            <p><strong>ID звонка:</strong> {{ $call->id }}</p>
                            <p><strong>Телефон:</strong> {{ $call->client_telephone }}</p>
                            <p><strong>Оператор:</strong> {{ optional($call->operator)->name ?: '-' }}</p>
                            <p><strong>Дата:</strong> {{ $call->created_at }}</p>
                            <p><strong>Направление:</strong> {{ $call->direction ?: '-' }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Анкета для общего отчёта</h3>
                        </div>
                        <form action="{{ $storeRoute }}" method="post">
                            @csrf
                            <input type="hidden" name="back" value="{{ $backUrl }}">
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="reason_key">Причина обращения</label>
                                    <select class="form-control" id="reason_key" name="reason_key" required>
                                        <option value="">Выберите причину</option>
                                        @foreach(($formConfig['reasons'] ?? []) as $reason)
                                            <option value="{{ $reason['k'] }}" {{ old('reason_key') === $reason['k'] ? 'selected' : '' }}>
                                                {{ $reason['l'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label id="modules_label">Модули / подтема</label>
                                    <div id="modules_box" class="border rounded p-3 bg-light">
                                        Сначала выберите причину обращения.
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="status_label">Статус</label>
                                    <select class="form-control" id="status_label" name="status_label" required>
                                        <option value="">Выберите статус</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="comment_text" id="comment_label">Комментарий</label>
                                    <textarea class="form-control" id="comment_text" name="comment_text" rows="5" placeholder="Добавьте детали разговора...">{{ old('comment_text') }}</textarea>
                                    <small class="form-text text-muted" id="comment_hint">Комментарий необязателен.</small>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary">Сохранить анкету</button>
                                <a href="{{ $backUrl }}" class="btn btn-default">Назад</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
const surveyConfig = @json($formConfig);
const oldState = {
    reason: @json(old('reason_key')),
    modules: @json(array_values((array) old('modules', []))),
    status: @json(old('status_label')),
};

const reasonSelect = document.getElementById('reason_key');
const modulesBox = document.getElementById('modules_box');
const modulesLabel = document.getElementById('modules_label');
const statusSelect = document.getElementById('status_label');
const commentInput = document.getElementById('comment_text');
const commentHint = document.getElementById('comment_hint');

function findReason(reasonKey) {
    return (surveyConfig.reasons || []).find((item) => item.k === reasonKey) || null;
}

function renderModules(reason) {
    modulesBox.innerHTML = '';

    if (!reason) {
        modulesLabel.textContent = 'Модули / подтема';
        modulesBox.textContent = 'Сначала выберите причину обращения.';
        return;
    }

    modulesLabel.textContent = reason.mTitle || 'Модули / подтема';

    if (!Array.isArray(reason.m) || reason.m.length === 0) {
        modulesBox.textContent = 'Для этой причины дополнительные модули не настроены.';
        return;
    }

    reason.m.forEach((moduleName) => {
        const wrapper = document.createElement('label');
        wrapper.className = 'd-block font-weight-normal';

        const input = document.createElement('input');
        input.type = 'checkbox';
        input.name = 'modules[]';
        input.value = moduleName;
        input.checked = oldState.modules.includes(moduleName);
        input.className = 'mr-2';

        wrapper.appendChild(input);
        wrapper.appendChild(document.createTextNode(moduleName));
        modulesBox.appendChild(wrapper);
    });
}

function renderStatuses(reason) {
    statusSelect.innerHTML = '';

    const empty = document.createElement('option');
    empty.value = '';
    empty.textContent = 'Выберите статус';
    statusSelect.appendChild(empty);

    if (!reason) {
        return;
    }

    (reason.s || []).forEach((statusName) => {
        const option = document.createElement('option');
        option.value = statusName;
        option.textContent = statusName;

        if (oldState.status === statusName) {
            option.selected = true;
        }

        statusSelect.appendChild(option);
    });
}

function renderCommentRequirement(reason) {
    const required = Boolean(reason && reason.req);

    commentInput.required = required;
    commentHint.textContent = required
        ? 'Для этой причины комментарий обязателен.'
        : 'Комментарий необязателен.';
}

function renderForm() {
    const reason = findReason(reasonSelect.value);
    renderModules(reason);
    renderStatuses(reason);
    renderCommentRequirement(reason);
}

reasonSelect.addEventListener('change', function () {
    oldState.modules = [];
    oldState.status = '';
    renderForm();
});

renderForm();
</script>
@endsection
