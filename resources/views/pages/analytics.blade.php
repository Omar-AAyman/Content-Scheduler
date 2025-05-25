@extends('layouts.app')

@section('title', 'Analytics - Content Scheduler')
@section('header_title', 'Analytics')

@section('content')
<div class="container">
    <!-- Summary Stats: Scheduled vs. Published Counts -->
    <div class="row mb-4">
        <div class="col-md-6 mb-4 mb-md-0">
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">Scheduled Posts</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0">{{ $scheduledCount }}</h2>
                            <p class="text-muted mb-0">Posts awaiting publication</p>
                        </div>
                        <div class="text-dark">
                            <i class="fas fa-calendar-alt fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">Published Posts</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0">{{ $publishedCount }}</h2>
                            <p class="text-muted mb-0">Posts successfully published</p>
                        </div>
                        <div class="text-success">
                            <i class="fas fa-check-circle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Publishing Success Rate -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">Publishing Success Rate</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Platform</th>
                                    <th>Total Posts</th>
                                    <th>Published Posts</th>
                                    <th>Success Rate</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($postsByPlatform as $platform)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="fab fa-{{ $platform['type'] }} fa-lg me-2"></i>
                                            {{ $platform['name'] }}
                                        </div>
                                    </td>
                                    <td>{{ $platform['count'] }}</td>
                                    <td>{{ $platform['published_count'] }}</td>
                                    <td>{{ number_format($platform['success_rate'], 1) }}%</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts: Scheduled vs. Published and Platform Distribution -->
    <div class="row mb-4">
        <div class="col-lg-8 mb-4 mb-lg-0">
            <div class="card">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Scheduled vs. Published Posts</h5>
                    <div class="btn-group">
                        <button type="button" class="btn btn-sm btn-outline-light active" data-range="week">Week</button>
                        <button type="button" class="btn btn-sm btn-outline-light" data-range="month">Month</button>
                        <button type="button" class="btn btn-sm btn-outline-light" data-range="year">Year</button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="height: 300px;">
                        <canvas id="performanceChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">Posts per Platform</h5>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="height: 200px;">
                        <canvas id="platformChart"></canvas>
                    </div>
                    <div class="mt-3">
                        @foreach($postsByPlatform as $platform)
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="d-flex align-items-center">
                                <div class="me-2" style="width: 12px; height: 12px; background-color: {{ $loop->index == 0 ? '#0d6efd' : ($loop->index == 1 ? '#f72585' : '#4cc9f0') }}; border-radius: 50%;"></div>
                                <span>{{ $platform['name'] }}</span>
                            </div>
                            <span class="badge bg-light text-dark">{{ $platform['percentage'] }}% ({{ $platform['count'] }})</span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Post Analytics Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Top Posts</h5>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-light dropdown-toggle" type="button" id="postsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            Last 30 Days
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end top-posts-dropdown" aria-labelledby="postsDropdown">
                            <li><a class="dropdown-item active" href="#" data-range="30days">Last 30 Days</a></li>
                            <li><a class="dropdown-item" href="#" data-range="90days">Last 90 Days</a></li>
                            <li><a class="dropdown-item" href="#" data-range="year">Last Year</a></li>
                            <li><a class="dropdown-item" href="#" data-range="all">All Time</a></li>
                        </ul>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Post</th>
                                    <th>Platform</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Success Rate</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topPosts as $post)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($post->image_url)
                                            <div class="post-image me-2">
                                                <img src="{{ $post->image_url }}" class="rounded" width="40" alt="Post image">
                                            </div>
                                            @endif
                                            <div>
                                                <div class="fw-semibold">{{ $post->title }}</div>
                                                <small class="text-muted">{{ Str::limit($post->content, 30) }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @foreach($post->platforms as $platform)
                                        <span class="badge bg-dark">{{ $platform->name }}</span>
                                        @endforeach
                                    </td>
                                    <td>{{ $post->scheduled_time->format('M d, Y') }}</td>
                                    <td>
                                        <span class="badge
                                            {{ $post->status == 'published' ? 'bg-success' :
                                               ($post->status == 'scheduled' ? 'bg-dark' : 'bg-secondary') }}">
                                            {{ ucfirst($post->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        @php
                                        $successCount = $post->platforms->where('pivot.platform_status', 'published')->count();
                                        $totalPlatforms = $post->platforms->count();
                                        $successRate = $totalPlatforms > 0 ? ($successCount / $totalPlatforms) * 100 : 0;
                                        @endphp
                                        <span>{{ number_format($successRate, 1) }}%</span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center">No posts found</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    window.analyticsData = {
        postsByPlatform: @json($postsByPlatform),
        weekData: @json($weekData),
        monthData: @json($monthData),
        yearData: @json($yearData)
    };
</script>
@endsection