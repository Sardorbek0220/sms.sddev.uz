<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Анкета по звонку #{{ $call->id }}</title>
    <link rel="stylesheet" href="{{ asset('assets/plugins/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/dist/css/adminlte.min.css') }}">
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            background:
                radial-gradient(circle at top right, rgba(14, 165, 233, 0.14), transparent 28%),
                linear-gradient(180deg, #eef4ff 0%, #f8fafc 100%);
            font-family: "Source Sans Pro", sans-serif;
            color: #0f172a;
        }
        .popup-shell {
            max-width: 980px;
            margin: 0 auto;
            padding: 18px;
        }
        .popup-hero {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            margin-bottom: 16px;
            padding: 18px 20px;
            border-radius: 22px;
            background: rgba(255, 255, 255, 0.94);
            border: 1px solid rgba(148, 163, 184, 0.18);
            box-shadow: 0 18px 36px rgba(15, 23, 42, 0.08);
        }
        .popup-title {
            margin: 0;
            font-size: 30px;
            font-weight: 800;
            line-height: 1.05;
        }
        .popup-subtitle {
            margin-top: 6px;
            color: #64748b;
            font-size: 14px;
            font-weight: 600;
        }
        .popup-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 14px;
            border-radius: 999px;
            background: #dbeafe;
            color: #1d4ed8;
            font-size: 13px;
            font-weight: 700;
        }
        .popup-grid {
            display: grid;
            grid-template-columns: 300px minmax(0, 1fr);
            gap: 16px;
        }
        .popup-card {
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.96);
            border: 1px solid rgba(148, 163, 184, 0.18);
            box-shadow: 0 16px 32px rgba(15, 23, 42, 0.08);
            overflow: hidden;
        }
        .popup-card-head {
            padding: 16px 18px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 18px;
            font-weight: 800;
        }
        .popup-card-body {
            padding: 18px;
        }
        .popup-meta {
            display: grid;
            gap: 12px;
        }
        .popup-meta-item {
            padding: 12px 14px;
            border-radius: 14px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
        }
        .popup-meta-label {
            display: block;
            margin-bottom: 4px;
            color: #64748b;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .popup-meta-value {
            font-size: 16px;
            font-weight: 700;
            color: #0f172a;
            word-break: break-word;
        }
        .popup-actions {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-top: 16px;
        }
        .popup-note {
            font-size: 13px;
            color: #64748b;
        }
        .popup-form .form-control {
            border-radius: 14px;
            border-color: #cbd5e1;
            box-shadow: none;
        }
        .popup-form textarea.form-control {
            min-height: 140px;
        }
        .popup-modules {
            border: 1px solid #dbe3ec;
            background: #f8fafc;
            border-radius: 14px;
            min-height: 88px;
            padding: 12px;
            color: #475569;
        }
        @media (max-width: 900px) {
            .popup-grid {
                grid-template-columns: 1fr;
            }
            .popup-title {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
<div class="popup-shell">
    <div class="popup-hero">
        <div>
            <h1 class="popup-title">Анкета по звонку</h1>
            <div class="popup-subtitle">Отдельное окно для быстрого заполнения по свежему звонку.</div>
        </div>
        <div class="popup-badge">
            <i class="fas fa-phone-alt"></i>
            Звонок #{{ $call->id }}
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="popup-grid">
        <div class="popup-card">
            <div class="popup-card-head">Информация о звонке</div>
            <div class="popup-card-body">
                <div class="popup-meta">
                    <div class="popup-meta-item">
                        <span class="popup-meta-label">Телефон</span>
                        <span class="popup-meta-value">{{ $call->client_telephone }}</span>
                    </div>
                    <div class="popup-meta-item">
                        <span class="popup-meta-label">Оператор</span>
                        <span class="popup-meta-value">{{ optional($call->operator)->name ?: '-' }}</span>
                    </div>
                    <div class="popup-meta-item">
                        <span class="popup-meta-label">Дата</span>
                        <span class="popup-meta-value">{{ $call->created_at }}</span>
                    </div>
                    <div class="popup-meta-item">
                        <span class="popup-meta-label">Направление</span>
                        <span class="popup-meta-value">{{ $call->direction ?: '-' }}</span>
                    </div>
                    <div class="popup-meta-item">
                        <span class="popup-meta-label">Разговор</span>
                        <span class="popup-meta-value">{{ gmdate('i:s', max((int) ($call->dialog_duration ?? 0), 0)) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="popup-card">
            <div class="popup-card-head">Анкета для общего отчёта</div>
            <div class="popup-card-body">
                <form action="{{ $storeRoute }}" method="post" class="popup-form">
                    @csrf
                    <input type="hidden" name="back" value="{{ $backUrl }}">
                    <input type="hidden" name="popup_window" value="1">

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
                        <div id="modules_box" class="popup-modules">
                            Сначала выберите причину обращения.
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="status_label">Статус</label>
                        <select class="form-control" id="status_label" name="status_label" required>
                            <option value="">Выберите статус</option>
                        </select>
                    </div>

                    <div class="form-group mb-0">
                        <label for="comment_text">Комментарий</label>
                        <textarea class="form-control" id="comment_text" name="comment_text" rows="5" placeholder="Добавьте детали разговора...">{{ old('comment_text') }}</textarea>
                        <small class="form-text text-muted" id="comment_hint">Комментарий необязателен.</small>
                    </div>

                    <div class="popup-actions">
                        <div class="popup-note">После сохранения окно закроется автоматически.</div>
                        <div class="d-flex flex-wrap" style="gap:10px;">
                            <button type="button" class="btn btn-default" onclick="window.close()">Закрыть</button>
                            <button type="submit" class="btn btn-primary">Сохранить анкету</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
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
    return (surveyConfig.reasons || []).find(function (item) {
        return item.k === reasonKey;
    }) || null;
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

    reason.m.forEach(function (moduleName) {
        const wrapper = document.createElement('label');
        wrapper.className = 'd-block font-weight-normal mb-1';

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

    (reason.s || []).forEach(function (statusName) {
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
</body>
</html>
