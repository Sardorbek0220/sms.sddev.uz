@php
    $widgetSurveyConfig = app(\App\Services\BitrixSurveyService::class)->getFormConfig();
@endphp

<div id="operatorLiveSurveyDock" class="operator-live-survey-dock">
  <div class="operator-live-survey-card shadow-lg" data-state="hidden">
    <div class="operator-live-survey-header">
      <div>
        <div class="operator-live-survey-eyebrow">Операторский виджет</div>
        <h4 class="operator-live-survey-title mb-0" id="operatorLiveSurveyTitle">Ожидаем новый звонок</h4>
      </div>
      <button type="button" class="btn btn-tool text-white" id="operatorLiveSurveyToggle">
        <i class="fas fa-chevron-down"></i>
      </button>
    </div>

    <div class="operator-live-survey-body" id="operatorLiveSurveyBody">
      <div class="operator-live-survey-status mb-3" id="operatorLiveSurveyMessage">
        Как только на вашего оператора придёт входящий звонок, окно откроется автоматически.
      </div>

      <div class="operator-live-survey-meta mb-3">
        <div><strong>Телефон:</strong> <span id="operatorLiveSurveyPhone">-</span></div>
        <div><strong>Дата:</strong> <span id="operatorLiveSurveyDate">-</span></div>
        <div><strong>Разговор:</strong> <span id="operatorLiveSurveyDuration">-</span></div>
        <div><strong>Статус:</strong> <span id="operatorLiveSurveyState">Ожидание</span></div>
      </div>

      <form method="post" id="operatorLiveSurveyForm">
        @csrf
        <input type="hidden" name="back" id="operatorLiveSurveyBack" value="{{ request()->fullUrl() }}">

        <div class="form-group">
          <label for="operator_live_reason_key">Причина обращения</label>
          <select class="form-control" id="operator_live_reason_key" name="reason_key" required>
            <option value="">Выберите причину</option>
            @foreach(($widgetSurveyConfig['reasons'] ?? []) as $reason)
              <option value="{{ $reason['k'] }}">{{ $reason['l'] }}</option>
            @endforeach
          </select>
        </div>

        <div class="form-group">
          <label id="operator_live_modules_label">Модули / подтема</label>
          <div id="operator_live_modules_box" class="operator-live-survey-modules">
            Сначала дождитесь звонка или выберите причину обращения.
          </div>
        </div>

        <div class="form-group">
          <label for="operator_live_status_label">Статус</label>
          <select class="form-control" id="operator_live_status_label" name="status_label" required>
            <option value="">Выберите статус</option>
          </select>
        </div>

        <div class="form-group mb-2">
          <label for="operator_live_comment_text">Комментарий</label>
          <textarea class="form-control" id="operator_live_comment_text" name="comment_text" rows="4" placeholder="Что произошло по звонку?"></textarea>
          <small class="form-text text-muted" id="operator_live_comment_hint">Комментарий не обязателен.</small>
        </div>

        <div class="d-flex flex-wrap justify-content-between align-items-center">
          <a href="{{ route('operator.report.calls') }}" class="btn btn-link px-0" id="operatorLiveSurveyReportLink">
            Открыть звонки
          </a>
          <button type="submit" class="btn btn-primary" id="operatorLiveSurveySubmit" disabled>
            Сохранить анкету
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<style>
  .operator-live-survey-dock {
    position: fixed;
    right: 16px;
    bottom: 16px;
    z-index: 1055;
    width: min(360px, calc(100vw - 24px));
  }

  .operator-live-survey-card {
    border-radius: 14px;
    overflow: hidden;
    background: #fff;
    color: #1f2937;
    border: 1px solid #e2e8f0;
    box-shadow: 0 12px 32px rgba(15,23,42,0.12);
    transition: transform .25s ease, opacity .25s ease, box-shadow .25s ease;
    transform: translateY(20px);
    opacity: 0;
    pointer-events: none;
  }

  .operator-live-survey-card[data-state="expanded"],
  .operator-live-survey-card[data-state="collapsed"] {
    transform: translateY(0);
    opacity: 1;
    pointer-events: auto;
  }

  .operator-live-survey-card[data-state="hidden"] {
    box-shadow: none;
  }

  .operator-live-survey-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 10px 14px;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    color: #1f2937;
  }
  .operator-live-survey-header .btn-tool { color: #64748b !important; }

  .operator-live-survey-eyebrow {
    font-size: 10px;
    letter-spacing: .12em;
    text-transform: uppercase;
    color: #64748b;
    margin-bottom: 2px;
  }

  .operator-live-survey-title {
    font-size: 15px;
    font-weight: 600;
    line-height: 1.2;
    color: #0f172a;
  }

  .operator-live-survey-body {
    padding: 14px;
    background: #fff;
    color: #1f2937;
  }

  .operator-live-survey-card[data-state="collapsed"] .operator-live-survey-body {
    display: none;
  }

  .operator-live-survey-status {
    border-radius: 12px;
    padding: 12px 14px;
    background: #e8eef9;
    color: #183153;
    font-size: 14px;
    line-height: 1.45;
  }

  .operator-live-survey-status.is-active {
    background: #fee2e2;
    color: #991b1b;
  }

  .operator-live-survey-status.is-pending {
    background: #fef3c7;
    color: #92400e;
  }

  .operator-live-survey-status.is-waiting {
    background: #dbeafe;
    color: #1d4ed8;
  }

  .operator-live-survey-meta {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px 14px;
    font-size: 13px;
  }

  .operator-live-survey-modules {
    border: 1px solid #dbe3ec;
    background: #fff;
    border-radius: 12px;
    min-height: 88px;
    padding: 12px;
    color: #495057;
  }

  @media (max-width: 576px) {
    .operator-live-survey-dock {
      right: 12px;
      left: 12px;
      bottom: 12px;
      width: auto;
    }

    .operator-live-survey-meta {
      grid-template-columns: 1fr;
    }
  }
</style>

<script>
  window.OperatorLiveSurveyWidget = (function () {
    var surveyConfig = @json($widgetSurveyConfig);
    var workspaceUrl = @json(route('operator.workspace.data'));
    var currentPopup = null;
    var pollTimer = null;
    var isCollapsed = false;
    var popupWindowRef = null;

    var card = document.querySelector('.operator-live-survey-card');
    var body = document.getElementById('operatorLiveSurveyBody');
    var title = document.getElementById('operatorLiveSurveyTitle');
    var message = document.getElementById('operatorLiveSurveyMessage');
    var phone = document.getElementById('operatorLiveSurveyPhone');
    var date = document.getElementById('operatorLiveSurveyDate');
    var duration = document.getElementById('operatorLiveSurveyDuration');
    var state = document.getElementById('operatorLiveSurveyState');
    var form = document.getElementById('operatorLiveSurveyForm');
    var backInput = document.getElementById('operatorLiveSurveyBack');
    var reportLink = document.getElementById('operatorLiveSurveyReportLink');
    var submitButton = document.getElementById('operatorLiveSurveySubmit');
    var toggleButton = document.getElementById('operatorLiveSurveyToggle');
    var reasonSelect = document.getElementById('operator_live_reason_key');
    var modulesBox = document.getElementById('operator_live_modules_box');
    var modulesLabel = document.getElementById('operator_live_modules_label');
    var statusSelect = document.getElementById('operator_live_status_label');
    var commentInput = document.getElementById('operator_live_comment_text');
    var commentHint = document.getElementById('operator_live_comment_hint');

    function getPopupWindowStateKey(popupKey) {
      return popupKey ? 'operator-live-survey-window-opened:' + popupKey : null;
    }

    function wasPopupWindowOpened(popupKey) {
      var stateKey = getPopupWindowStateKey(popupKey);
      return Boolean(stateKey && window.sessionStorage.getItem(stateKey));
    }

    function markPopupWindowOpened(popupKey) {
      var stateKey = getPopupWindowStateKey(popupKey);
      if (stateKey) {
        window.sessionStorage.setItem(stateKey, String(Date.now()));
      }
    }

    function clearPopupWindowOpened(popupKey) {
      var stateKey = getPopupWindowStateKey(popupKey);
      if (stateKey) {
        window.sessionStorage.removeItem(stateKey);
      }
    }

    function openSurveyPopupWindow(popup) {
      if (!popup || !popup.key || !popup.popup_window_allowed || !popup.popup_window_url || !popup.can_submit) {
        return;
      }

      if (wasPopupWindowOpened(popup.key)) {
        return;
      }

      var features = [
        'popup=yes',
        'width=860',
        'height=920',
        'menubar=no',
        'toolbar=no',
        'location=no',
        'status=no',
        'resizable=yes',
        'scrollbars=yes'
      ].join(',');

      var nextWindow = window.open(
        popup.popup_window_url,
        'operatorSurveyWindow_' + popup.key.replace(/[^a-z0-9_-]/ig, '_'),
        features
      );

      if (nextWindow && !nextWindow.closed) {
        popupWindowRef = nextWindow;
        markPopupWindowOpened(popup.key);

        try {
          nextWindow.focus();
        } catch (error) {
          console.error(error);
        }
      }
    }

    function escapeHtml(value) {
      return String(value || '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
    }

    function findReason(reasonKey) {
      return (surveyConfig.reasons || []).find(function (item) {
        return item.k === reasonKey;
      }) || null;
    }

    function getDraftKey() {
      return currentPopup && currentPopup.key
        ? 'operator-live-survey-draft:' + currentPopup.key
        : null;
    }

    function readDraft() {
      var draftKey = getDraftKey();

      if (!draftKey) {
        return null;
      }

      try {
        return JSON.parse(localStorage.getItem(draftKey) || 'null');
      } catch (error) {
        console.error(error);
        return null;
      }
    }

    function writeDraft() {
      var draftKey = getDraftKey();

      if (!draftKey) {
        return;
      }

      var modules = Array.prototype.slice.call(document.querySelectorAll('#operator_live_modules_box input[name="modules[]"]:checked'))
        .map(function (input) {
          return input.value;
        });

      var draft = {
        reason_key: reasonSelect.value,
        status_label: statusSelect.value,
        comment_text: commentInput.value,
        modules: modules,
      };

      localStorage.setItem(draftKey, JSON.stringify(draft));
    }

    function renderModules(reason, selectedModules) {
      modulesBox.innerHTML = '';

      if (!reason) {
        modulesLabel.textContent = 'Модули / подтема';
        modulesBox.textContent = currentPopup
          ? 'Сначала выберите причину обращения.'
          : 'Сначала дождитесь звонка или выберите причину обращения.';
        return;
      }

      modulesLabel.textContent = reason.mTitle || 'Модули / подтема';

      if (!Array.isArray(reason.m) || reason.m.length === 0) {
        modulesBox.textContent = 'Для этой причины дополнительные модули не настроены.';
        return;
      }

      reason.m.forEach(function (moduleName) {
        var wrapper = document.createElement('label');
        wrapper.className = 'd-block font-weight-normal mb-1';

        var input = document.createElement('input');
        input.type = 'checkbox';
        input.name = 'modules[]';
        input.value = moduleName;
        input.checked = selectedModules.indexOf(moduleName) !== -1;
        input.className = 'mr-2';
        input.disabled = !currentPopup;
        input.addEventListener('change', writeDraft);

        wrapper.appendChild(input);
        wrapper.appendChild(document.createTextNode(moduleName));
        modulesBox.appendChild(wrapper);
      });
    }

    function renderStatuses(reason, selectedStatus) {
      statusSelect.innerHTML = '<option value="">Выберите статус</option>';

      if (!reason) {
        return;
      }

      (reason.s || []).forEach(function (statusName) {
        var option = document.createElement('option');
        option.value = statusName;
        option.textContent = statusName;

        if (selectedStatus === statusName) {
          option.selected = true;
        }

        statusSelect.appendChild(option);
      });
    }

    function renderCommentRequirement(reason) {
      var required = Boolean(reason && reason.req);
      commentInput.required = required;
      commentHint.textContent = required
        ? 'Для этой причины комментарий обязателен.'
        : 'Комментарий не обязателен.';
    }

    function renderFormState() {
      var draft = readDraft() || {};
      var reason = findReason(reasonSelect.value);
      renderModules(reason, Array.isArray(draft.modules) ? draft.modules : []);
      renderStatuses(reason, draft.status_label || '');
      renderCommentRequirement(reason);
      statusSelect.disabled = !currentPopup;
      commentInput.disabled = !currentPopup;
      reasonSelect.disabled = !currentPopup;
      submitButton.disabled = !currentPopup || !currentPopup.can_submit;
    }

    function setCardState(nextState) {
      card.setAttribute('data-state', nextState);
      toggleButton.innerHTML = nextState === 'collapsed'
        ? '<i class="fas fa-chevron-up"></i>'
        : '<i class="fas fa-chevron-down"></i>';
    }

    function hideWidget() {
      if (currentPopup && currentPopup.key) {
        clearPopupWindowOpened(currentPopup.key);
      }

      currentPopup = null;
      setCardState('hidden');
      title.textContent = 'Ожидаем новый звонок';
      message.className = 'operator-live-survey-status';
      message.textContent = 'Как только на вашего оператора придёт входящий звонок, окно откроется автоматически.';
      phone.textContent = '-';
      date.textContent = '-';
      duration.textContent = '-';
      state.textContent = 'Ожидание';
      form.action = '';
      reportLink.href = @json(route('operator.report.calls'));
      reportLink.textContent = 'Открыть звонки';
      reasonSelect.value = '';
      statusSelect.innerHTML = '<option value="">Выберите статус</option>';
      modulesBox.textContent = 'Сначала дождитесь звонка или выберите причину обращения.';
      commentInput.value = '';
      renderFormState();
    }

    function syncDraftIntoForm() {
      var draft = readDraft() || {};
      reasonSelect.value = draft.reason_key || '';
      commentInput.value = draft.comment_text || '';
      renderFormState();
    }

    function renderPopup(popup) {
      currentPopup = popup || null;

      if (!popup || popup.has_survey) {
        hideWidget();
        return;
      }

      title.textContent = popup.title || 'Анкета по звонку';
      phone.textContent = popup.phone || '-';
      date.textContent = popup.created_at || '-';
      duration.textContent = popup.dialog_duration_human || popup.call_duration_human || '-';
      state.textContent = popup.is_active ? 'Разговор идёт' : (popup.can_submit ? 'Нужно заполнить' : 'Ждём запись');
      form.action = popup.store_url || '';
      backInput.value = window.location.href;
      reportLink.href = popup.popup_window_allowed && popup.popup_window_url
        ? popup.popup_window_url
        : (popup.report_url || @json(route('operator.report.calls')));
      reportLink.textContent = popup.popup_window_allowed && popup.popup_window_url
        ? 'Открыть окно анкеты'
        : 'Открыть звонки';

      message.className = 'operator-live-survey-status ' + (
        popup.is_active ? 'is-active' : (popup.can_submit ? 'is-pending' : 'is-waiting')
      );
      message.textContent = popup.message || '';

      syncDraftIntoForm();
      openSurveyPopupWindow(popup);

      if (!isCollapsed) {
        setCardState('expanded');
      } else {
        setCardState('collapsed');
      }
    }

    function refreshWorkspaceData() {
      fetch(workspaceUrl, {
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json'
        },
        credentials: 'same-origin'
      })
        .then(function (response) {
          if (!response.ok) {
            throw new Error('Workspace response failed: ' + response.status);
          }

          return response.json();
        })
        .then(function (data) {
          window.dispatchEvent(new CustomEvent('operator-workspace:update', {
            detail: data
          }));

          if (data.popup_call) {
            var shouldAutoExpand = !currentPopup || currentPopup.key !== data.popup_call.key || data.popup_call.is_active;
            if (shouldAutoExpand) {
              isCollapsed = false;
            }
            renderPopup(data.popup_call);
          } else {
            hideWidget();
          }
        })
        .catch(function (error) {
          console.error(error);
        });
    }

    toggleButton.addEventListener('click', function () {
      if (card.getAttribute('data-state') === 'collapsed') {
        isCollapsed = false;
        setCardState('expanded');
        return;
      }

      if (card.getAttribute('data-state') === 'expanded') {
        isCollapsed = true;
        setCardState('collapsed');
      }
    });

    reasonSelect.addEventListener('change', function () {
      var draftKey = getDraftKey();
      var draft = readDraft() || {};
      draft.reason_key = reasonSelect.value;
      draft.modules = [];
      draft.status_label = '';
      if (draftKey) {
        localStorage.setItem(draftKey, JSON.stringify(draft));
      }
      renderFormState();
    });
    statusSelect.addEventListener('change', writeDraft);
    commentInput.addEventListener('input', writeDraft);

    document.addEventListener('DOMContentLoaded', function () {
      hideWidget();
      refreshWorkspaceData();
      pollTimer = window.setInterval(refreshWorkspaceData, 5000);
    });

    return {
      refresh: refreshWorkspaceData
    };
  })();
</script>
