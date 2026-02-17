<nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button">
                <i class="fas fa-bars"></i>
            </a>
        </li>
    </ul>

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
        @if (Auth::guard('usuario')->check() && Auth::guard('usuario')->user()->isSisAdmin())
            <li class="nav-item">
                <a class="nav-link" data-widget="fullscreen" href="#" role="button">
                    <i class="fas fa-expand-arrows-alt"></i>
                </a>
            </li>
        @endif

        <li class="nav-item dropdown">
            <a class="nav-link" data-toggle="dropdown" href="#">
                <span>{{ Auth::guard('usuario')->user()->nombre }}</span>
            </a>
            <div class="dropdown-menu dropdown-menu-right">
                <form method="POST" action="{{ route('auth.logout') }}">
                    @csrf
                    <button type="submit" class="dropdown-item">
                        <i class="fas fa-sign-out-alt mr-2"></i> Cerrar Sesión
                    </button>
                </form>
            </div>
        </li>
    </ul>
</nav>