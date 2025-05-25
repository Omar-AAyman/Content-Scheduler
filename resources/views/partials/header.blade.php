<header class="header mb-2">
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="header-title">@yield('header_title', 'Dashboard')</h1>
        <div>
            <button id="mobileSidebarToggle" class="btn btn-dark d-md-none">
                <i class="fas fa-bars"></i>
            </button>
            <div class="dropdown d-inline-block">
                <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-user me-1"></i>{{ Auth::user()->name ?? 'User' }}
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                    <li>
                        <a class="dropdown-item {{ Route::is('profile.show') ? 'active' : '' }}" href="{{ route('profile.show') }}">
                            <i class="fas fa-user me-2"></i> Profile
                        </a>
                    </li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="dropdown-item"><i class="fas fa-sign-out-alt me-2"></i>Logout</button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</header>
