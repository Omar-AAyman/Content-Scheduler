@extends('layouts.app')

@section('title', 'Platform Settings - Content Scheduler')
@section('header_title', 'Platform Settings')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">Manage Platforms</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Platform</th>
                                    <th>Status</th>
                                    <th>Character Limit</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($platforms as $platform)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="fab fa-{{ $platform->type }} fa-lg me-2"></i>
                                            <span>{{ $platform->name }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input platform-toggle" type="checkbox"
                                                   id="{{ $platform->type }}Toggle"
                                                   {{ $platform->users->where('id', Auth::id())->first() && $platform->users->where('id', Auth::id())->first()->pivot->is_active ? 'checked' : '' }}
                                                   data-platform-id="{{ $platform->id }}">
                                        </div>
                                    </td>
                                    <td>
                                        <span>{{ $platform->max_content_length ?? 'Not set' }}</span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">Rate Limits</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">You can schedule up to 10 posts per day.</p>
                    <div class="progress mb-3" style="height: 20px;">
                        <div class="progress-bar
                                    {{ $dailyPostCount <= 3 ? 'bg-success' :
                                       ($dailyPostCount <= 7 ? 'bg-warning' : 'bg-danger') }}"
                             role="progressbar"
                             style="width: {{ $dailyPostCount * 10 }}%;"
                             aria-valuenow="{{ $dailyPostCount }}"
                             aria-valuemin="0"
                             aria-valuemax="10">
                            {{ $dailyPostCount }}/10
                        </div>
                    </div>
                    <p class="text-center">
                        <small class="text-muted">Posts scheduled today: {{ $dailyPostCount }}</small>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
