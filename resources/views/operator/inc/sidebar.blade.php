<aside class="main-sidebar sidebar-dark-primary elevation-4">
  <div class="sidebar">
    <div class="user-panel mt-3 pb-3 mb-3 d-flex">
      <div class="image">
        <img src="{{ asset('assets/logo.png') }}" class="img-circle elevation-2" alt="User Image">
      </div>
      <div class="info">
        <a href="{{ route('operator.workspace') }}" class="d-block">
          {{ auth()->user()->name }}
        </a>
      </div>
    </div>

    <nav class="mt-2">
      <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
        <li class="nav-item">
          <a href="{{ route('operator.workspace') }}" class="nav-link {{ request()->routeIs('operator.workspace') ? 'active' : '' }}">
            <i class="nav-icon fas fa-th-large"></i>
            <p>Моё окно</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="{{ route('operator.report.calls') }}" class="nav-link {{ request()->routeIs('operator.report.calls') ? 'active' : '' }}">
            <i class="nav-icon fas fa-phone-alt"></i>
            <p>Мои звонки</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="{{ route('operator.calls-no-anketa') }}" class="nav-link {{ request()->routeIs('operator.calls-no-anketa') ? 'active' : '' }}">
            <i class="nav-icon fas fa-exclamation-circle"></i>
            <p>Без анкеты</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="{{ route('operator.monitoring') }}" class="nav-link {{ request()->routeIs('operator.monitoring') ? 'active' : '' }}">
            <i class="nav-icon fas fa-tachometer-alt"></i>
            <p>Мониторинг</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="{{ route('operator.settings') }}" class="nav-link {{ request()->routeIs('operator.settings') ? 'active' : '' }}">
            <i class="nav-icon fas fa-cog"></i>
            <p>Настройки</p>
          </a>
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
