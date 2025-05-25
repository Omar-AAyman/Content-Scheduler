<div class="sidebar col-md-3 col-lg-2 d-md-block bg-dark">
    <div class="sidebar-brand d-flex align-items-center">
        <i class="fas fa-calendar-alt me-2"></i>
        <span>Content Scheduler</span>
    </div>

    <ul class="sidebar-nav mt-3">
        <li class="sidebar-nav-item">
            <a href="{{ route('dashboard') }}" class="sidebar-nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="fas fa-home sidebar-nav-icon"></i>
                Dashboard
            </a>
        </li>
        <li class="sidebar-nav-item">
            @php
            $hasActivePlatforms = Auth::user()->platforms()->wherePivot('is_active', true)->exists();
            @endphp
            @if($hasActivePlatforms)
            <a href="{{ route('posts.create') }}" class="sidebar-nav-link {{ request()->routeIs('posts.create') ? 'active' : '' }}">
                <i class="fas fa-plus sidebar-nav-icon"></i>
                New Post
            </a>
            @else
            <a class="sidebar-nav-link text-danger" title="No active platforms. Please activate a platform first." aria-disabled="true">
                <i class="fas fa-plus sidebar-nav-icon"></i>
                New Post
            </a>
            @endif
        </li>
        <li class="sidebar-nav-item">
            <a href="{{ route('platforms.index') }}" class="sidebar-nav-link {{ request()->routeIs('platforms.index') ? 'active' : '' }}">
                <i class="fas fa-share-alt sidebar-nav-icon"></i>
                Platforms
            </a>
        </li>
        <li class="sidebar-nav-item">
            <a href="{{ route('analytics.index') }}" class="sidebar-nav-link {{ request()->routeIs('analytics.index') ? 'active' : '' }}">
                <i class="fas fa-chart-bar sidebar-nav-icon"></i>
                Analytics
            </a>
        </li>
    </ul>

    <div class="mt-auto p-3">
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-outline-light btn-sm w-100">
                <i class="fas fa-sign-out-alt me-2"></i>Logout
            </button>
        </form>
    </div>
</div>
