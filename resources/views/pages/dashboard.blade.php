@extends('layouts.app')

@section('title', 'Dashboard - Content Scheduler')
@section('header_title', 'Dashboard')

@section('content')
    <div class="container">
        <!-- View Toggle Buttons -->
        <div class="mb-4 d-flex justify-content-end">
            <button id="viewCalendar" class="btn btn-outline-dark me-2">Calendar View</button>
            <button id="viewList" class="btn btn-dark">List View</button>
        </div>

        <!-- Filter Posts -->
        <div class="card mb-4">
            <div class="card-header bg-dark text-white">Filter Posts</div>
            <div class="card-body">
                <form method="GET" action="{{ route('dashboard') }}">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select name="status" id="status" class="form-select">
                                <option value="all" {{ $status == 'all' ? 'selected' : '' }}>All</option>
                                <option value="draft" {{ $status == 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="scheduled" {{ $status == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                                <option value="published" {{ $status == 'published' ? 'selected' : '' }}>Published</option>
                                <option value="failed" {{ $status == 'failed' ? 'selected' : '' }}>Failed</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="date" name="start_date" id="start_date" class="form-control" value="{{ $start_date ?? '' }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="end_date" class="form-label">End Date</label>
                            <input type="date" name="end_date" id="end_date" class="form-control" value="{{ $end_date ?? '' }}">
                        </div>
                    </div>
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-dark me-2">Apply Filters</button>
                        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">Clear Filters</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Post Statistics -->
        <div class="card mb-4">
            <div class="card-header bg-dark text-white">Post Statistics</div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-3 mb-3">
                        <div class="alert alert-secondary">Drafts: {{ $draftCount }}</div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="alert alert-primary">Scheduled: {{ $scheduledCount }}</div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="alert alert-success">Published: {{ $publishedCount }}</div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="alert alert-danger">Failed: {{ $failedCount }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Calendar View -->
        <div id="calendarView" style="display: none;">
            <div class="card mb-4">
                <div class="card-header bg-dark text-white">Calendar View</div>
                <div class="card-body">
                    <div id="calendar" class="fc-responsive"></div>
                </div>
            </div>
        </div>

        <!-- List View -->
        <div id="listView">
            <div class="card mb-4">
                <div class="card-header bg-dark text-white">Posts</div>
                <div class="card-body">
                    @if($posts->isEmpty())
                        <p class="text-muted">No posts found.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Status</th>
                                        <th>Scheduled Time</th>
                                        <th>Platforms</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($posts as $post)
                                        <tr>
                                            <td>{{ $post->title }}</td>
                                            <td>
                                                <span class="badge
                                                    {{ $post->status == 'published' ? 'bg-success' :
                                                       ($post->status == 'scheduled' ? 'bg-dark' :
                                                       ($post->status == 'draft' ? 'bg-secondary' : 'bg-danger')) }}">
                                                    {{ ucfirst($post->status) }}
                                                </span>
                                            </td>
                                            <td>{{ $post->scheduled_time->format('Y-m-d H:i') }}</td>
                                            <td>
                                                @foreach($post->platforms as $platform)
                                                    {{ $platform->name }}{{ !$loop->last ? ', ' : '' }}
                                                @endforeach
                                            </td>
                                            <td>
                                                <a href="{{  route('posts.show', $post) }}"
                                                   class="btn btn-sm btn-outline-primary">View</a>
                                                   @if($post->status !== 'published')
                                                        @if($hasActivePlatforms)
                                                            <a href="{{ route('posts.edit', $post) }}"
                                                                class="btn btn-sm btn-outline-secondary">Edit</a>
                                                        @else
                                                            <button class="btn btn-sm btn-outline-secondary disabled"
                                                                    title="No active platforms. Please activate a platform first."
                                                                    aria-disabled="true">
                                                                Edit
                                                            </button>
                                                        @endif
                                                   @endif
                                                <form action="{{ route('posts.destroy', $post) }}" method="POST" style="display:inline;"
                                                      onsubmit="return confirm('Are you sure you want to delete this post?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">
                            Showing {{ $posts->firstItem() }} to {{ $posts->lastItem() }} of {{ $posts->total() }} entries
                            {{ $posts->appends(['status' => $status, 'start_date' => $start_date, 'end_date' => $end_date])->links('pagination::bootstrap-5') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="card">
            <div class="card-header bg-dark text-white">Recent Activity</div>
            <div class="card-body">
                @if($activities->isEmpty())
                    <p class="text-muted">No recent activity.</p>
                @else
                    <div id="activityList" class="activity-list">
                        @foreach($activities as $activity)
                            <div data-activity-id="{{ $activity->id }}" class="mb-2">
                                {{ ucfirst($activity->details) }} — {{ $activity->created_at->diffForHumans() }}
                            </div>
                        @endforeach
                    </div>
                    @if($activities->count() > 0)
                        <button id="toggleActivities" class="btn btn-sm btn-outline-dark mt-3">Show More</button>
                    @endif
                @endif
            </div>
        </div>
    </div>
@endsection

@section('styles')
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css" rel="stylesheet">
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
    <script>
        window.dashboardData = {
            posts: [
                @foreach($posts as $post)
                    {
                        title: {{ Illuminate\Support\Js::from($post->title) }},
                        scheduled_time: '{{ $post->scheduled_time->format('Y-m-d\TH:i:s') }}',
                        url: '{{ route('posts.show', $post) }}',
                        status: '{{ $post->status }}'
                    }@if(!$loop->last),@endif
                @endforeach
            ]
        };
    </script>
@endsection