<aside class="main-sidebar sidebar-dark-primary elevation-4">
  <div class="sidebar">
    <div class="user-panel mt-3 pb-3 mb-3 d-flex">
      <div class="image">
        <img src="{{ asset('assets/logo.png')}}" class="img-circle elevation-2" alt="User Image">
      </div>
      <div class="info">
        <a href="{{ route('admin.profile', auth()->user()->id) }}" class="d-block">
            {{ auth()->user()->name }}
        </a>
      </div>
    </div>

    <nav class="mt-2">
      <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
        @if(auth()->user()->hasPermission('view_dashboard'))
        <li class="nav-item">
          <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->is('admin/dashboard') ? 'active' : '' }}">
            <i class="nav-icon fas fa-th-large"></i>
            <p>Дашборд</p>
          </a>
        </li>
        @endif
        @if(auth()->user()->hasPermission('view_survey_reports'))
        <li class="nav-item">
          <a href="{{ route('admin.anketi') }}" class="nav-link {{ request()->is('admin/anketi') ? 'active' : '' }}" style="background:#1f7a5a;">
            <i class="nav-icon fas fa-clipboard-check"></i>
            <p>Анкеты</p>
          </a>
        </li>
        @endif
        @if(auth()->user()->hasPermission('view_survey_reports'))
        <li class="nav-item">
          <a href="{{ route('admin.calls-no-anketa') }}" class="nav-link {{ request()->is('admin/calls-no-anketa') ? 'active' : '' }}">
            <i class="nav-icon fas fa-exclamation-circle"></i>
            <p>
              Без анкеты
              @php try { $pending = \App\Http\Controllers\Admin\CallsWithoutSurveyController::pendingCount(); } catch (\Throwable $e) { $pending = 0; } @endphp
              @if($pending > 0)
                <span class="badge" style="background:#ef4444;color:#fff;border-radius:999px;padding:1px 8px;font-size:11px;margin-left:6px;">{{ $pending > 999 ? '999+' : $pending }}</span>
              @endif
            </p>
          </a>
        </li>
        @endif
        @if(auth()->user()->hasPermission('view_survey_settings'))
        <li class="nav-item">
          <a href="{{ route('admin.survey-settings') }}" class="nav-link {{ request()->is('admin/survey-settings') ? 'active' : '' }}">
            <i class="nav-icon fas fa-cogs"></i>
            <p>Настройки анкеты</p>
          </a>
        </li>
        @endif
        <li class="nav-item">
          <a href="#" onclick="window.open('/admin/monitoring')" class="nav-link">
            <i class="nav-icon fas fa-tachometer-alt"></i>
            <p>Мониторинг</p>
          </a>
        </li>
        @if(auth()->user()->hasPermission('view_calls'))
        <li class="nav-item">
          <a href="{{ route('admin.report.calls') }}" class="nav-link">
            <i class="nav-icon fas fa-phone-alt"></i>
            <p>Звонки</p>
          </a>
        </li>
        @endif
        @if(auth()->user()->hasPermission('view_callback_analytics'))
        <li class="nav-item">
          <a href="{{ route('admin.report.callback-analytics') }}" class="nav-link">
            <i class="nav-icon fas fa-chart-line"></i>
            <p>Аналитика звонков</p>
          </a>
        </li>
        @endif
        @if(auth()->user()->hasPermission('view_feedback'))
        <li class="nav-item">
          <a href="{{ route('feedback.all') }}" class="nav-link">
            <i class="nav-icon fas fa-mail-bulk"></i>
            <p>Отзывы клиентов</p>
          </a>
        </li>
        @endif
        @if(auth()->user()->hasPermission('view_operators'))
        <li class="nav-item">
          <a href="{{ route('operators.index') }}" class="nav-link">
            <i class="nav-icon fas fa-users-cog"></i>
            <p>Операторы</p>
          </a>
        </li>
        @endif
        @if(auth()->user()->hasPermission('view_users'))
        <li class="nav-item">
          <a href="{{ route('admin.users.index') }}" class="nav-link">
            <i class="nav-icon fas fa-user-shield"></i>
            <p>Пользователи</p>
          </a>
        </li>
        @endif
        @if(auth()->user()->hasPermission('view_work_hours'))
        <li class="nav-item">
          <a href="{{ route('admin.work-hours') }}" class="nav-link {{ request()->is('admin/work-hours') ? 'active' : '' }}">
            <i class="nav-icon fas fa-clock"></i>
            <p>Рабочие часы</p>
          </a>
        </li>
        @endif
        @if(auth()->user()->hasPermission('view_audit_log'))
        <li class="nav-item">
          <a href="{{ route('admin.audit-log') }}" class="nav-link {{ request()->is('admin/audit-log') ? 'active' : '' }}">
            <i class="nav-icon fas fa-history"></i>
            <p>Журнал действий</p>
          </a>
        </li>
        @if(auth()->user()->hasPermission('view_access_control'))
        <li class="nav-item">
          <a href="{{ route('admin.access-control') }}" class="nav-link {{ request()->is('admin/access-control') ? 'active' : '' }}">
            <i class="nav-icon fas fa-user-shield"></i>
            <p>Доступы</p>
          </a>
        </li>
        @endif
        @endif
        <li class="nav-item">
          <a href="#" class="nav-link">
            <i class="nav-icon fas fa-chart-bar"></i>
            <p>Автоматизация<i class="fas fa-angle-left right"></i></p>
          </a>
          <ul class="nav nav-treeview">
            @if(auth()->user()->hasPermission('view_bigreport'))
        <li class="nav-item">
              <a href="{{ route('admin.bigreport') }}" class="nav-link" style="background: dimgrey;">
                <i class="nav-icon fas fa-th-list"></i>
                <p>Дашборд</p>
              </a>
            </li>
        @endif
            <li class="nav-item">
              <a href="{{ route('likes.index') }}" class="nav-link" style="background: dimgrey;">
                <i class="nav-icon fas fa-user-plus"></i>
                <p>Нравится / Наказание</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{ route('products.index') }}" class="nav-link" style="background: dimgrey;">
                <i class="nav-icon fas fa-user-check"></i>
                <p>Скрипт / Продукт</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{ route('trainings.index') }}" class="nav-link" style="background: dimgrey;">
                <i class="nav-icon fas fa-book"></i>
                <p>Обучение</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{ route('scores.index') }}" class="nav-link" style="background: dimgrey;">
                <i class="nav-icon fas fa-tools"></i>
                <p>Установить баллы</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{ route('exceptions.index') }}" class="nav-link" style="background: dimgrey;">
                <i class="nav-icon fas fa-history"></i>
                <p>Исключения</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{ route('holidays.index') }}" class="nav-link" style="background: dimgrey;">
                <i class="nav-icon fas fa-gift"></i>
                <p>Праздники</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{ route('admin.tablereport') }}" class="nav-link" style="background: dimgrey;">
                <i class="nav-icon fas fa-table"></i>
                <p>Табличный отчет</p>
              </a>
            </li>
          </ul>
        </li>
        <li class="nav-item">
          <a href="{{ route('logout') }}" class="nav-link">
            <i class="nav-icon fas fa-window-close"></i>
            <p>Выйти</p>
          </a>
        </li>
      </ul>
    </nav>
  </div>
</aside>
