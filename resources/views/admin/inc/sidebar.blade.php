<aside class="main-sidebar sidebar-dark-primary elevation-4">
  <div class="sidebar">
    <div class="user-panel mt-3 pb-3 mb-3 d-flex">
      <div class="image">
        <img src="{{ asset('assets/logo.png')}}" class="img-circle elevation-2" alt="User Image">
      </div>
      <div class="info">
        <a href="{{ route('admin.profile', auth()->user()->id) }}" class="d-block">
            {{auth()->user()->name}} 
        </a>
      </div>
    </div>

    <nav class="mt-2">
      <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
        <li class="nav-item">
          <a href="#" onclick="window.open('/admin/monitoring')" class="nav-link">
            <i class="nav-icon fas fa-tachometer-alt"></i>
            <p>
              {{__('Monitoring')}}
            </p>
          </a>
        </li>
        <li class="nav-item">
          <a href="{{ route('admin.report') }}" class="nav-link">
            <i class="nav-icon fas fa-file-alt"></i>
            <p>
              {{__('Reports')}}
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
          <a href="{{ route('operators.index') }}" class="nav-link">
            <i class="nav-icon fas fa-users-cog"></i>
            <p>
              {{__('Operators')}}
            </p>
          </a>
        </li>
        <li class="nav-item">
          <a href="#" class="nav-link">
            <i class="nav-icon fas fa-chart-bar"></i>
            <p>Automatization<i class="fas fa-angle-left right"></i></p>
          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="#" onclick="window.open('/admin/bigreport')" class="nav-link" style="background: dimgrey;">
                <i class="nav-icon fas fa-th-list"></i>
                <p>
                  {{__('Big report')}}
                </p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{ route('likes.index') }}" class="nav-link" style="background: dimgrey;">
                <i class="nav-icon fas fa-user-plus"></i>
                <p>
                  {{__('Like / Punishment')}}
                </p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{ route('products.index') }}" class="nav-link" style="background: dimgrey;">
                <i class="nav-icon fas fa-user-check"></i>
                <p>
                  {{__('Script / Product')}}
                </p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{ route('admin.piece') }}" class="nav-link" style="background: dimgrey;">
                <i class="nav-icon fas fa-th-list"></i>
                <p>
                  {{__('Pieces')}}
                </p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{ route('scores.index') }}" class="nav-link" style="background: dimgrey;">
                <i class="nav-icon fas fa-tools"></i>
                <p>
                  {{__('Scores')}}
                </p>
              </a>
            </li>
          </ul>
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