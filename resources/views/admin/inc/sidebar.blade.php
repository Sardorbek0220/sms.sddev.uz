<aside class="main-sidebar sidebar-dark-primary elevation-4">
  <div class="sidebar">
    <div class="user-panel mt-3 pb-3 mb-3 d-flex">
      <div class="image">
        <img src="{{ asset('assets/logo.png')}}" class="img-circle elevation-2" alt="User Image">
      </div>
      <div class="info">
        <a href="{{ route('admin.profile') }}" class="d-block">
            {{auth()->user()->name}} 
        </a>
      </div>
    </div>

    <nav class="mt-2">
      <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
        <li class="nav-item">
          <a href="{{ route('operators.index') }}" class="nav-link">
            <i class="nav-icon fas fa-users-cog"></i>
            <p>
              {{__('Operators')}}
            </p>
          </a>
        </li>
        <li class="nav-item">
          <a href="{{ route('feedback.all') }}" class="nav-link">
            <i class="nav-icon fas fa-mail-bulk"></i>
            <p>
              {{__('Feedback from clients')}}
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