<aside class="main-sidebar sidebar-dark-primary elevation-4">
  <div class="sidebar">
    <div class="user-panel mt-3 pb-3 mb-3 d-flex">
      <div class="image">
        <img src="{{ asset('assets/logo.png')}}" class="img-circle elevation-2" alt="User Image">
      </div>
      <div class="info">
        <a href="#" class="d-block">
            SD Operator
        </a>
      </div>
    </div>

    <nav class="mt-2">
      <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
        <li class="nav-item">
          <a href="#" onclick="window.open('/operator/monitoring')" class="nav-link">
            <i class="nav-icon fas fa-tachometer-alt"></i>
            <p>
              {{__('Monitoring')}}
            </p>
          </a>
        </li>
        <li class="nav-item">
          <a href="{{ route('operator.bigreport') }}" class="nav-link">
            <i class="nav-icon fas fa-th-list"></i>
            <p>
              {{__('Big report')}}
            </p>
          </a>
        </li>
        
        <li class="nav-item">
          <a href="{{ route('logout') }}" class="nav-link">
            <i class="nav-icon fas fa-window-close"></i>
            <p>{{__('Log out')}}</p>
          </a>
        </li>
      </ul>
    </nav>
  </div>
</aside>