@extends('layouts.app')

@section('title', 'View Post - Content Scheduler')
@section('header_title', 'View Post')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">Post Details</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6 class="fw-bold">Title</h6>
                        <p>{{ $post->title }}</p>
                    </div>

                    <div class="mb-3">
                        <h6 class="fw-bold">Content</h6>
                        <p>{{ $post->content }}</p>
                    </div>

                    @if($post->image_url)
                        <div class="mb-3">
                            <h6 class="fw-bold">Image</h6>
                            <img src="{{ $post->image_url }}" alt="Post Image" class="img-fluid rounded" style="max-width: 300px;">
                        </div>
                    @endif

                    <div class="mb-3">
                        <h6 class="fw-bold">Scheduled Time</h6>
                        <p>{{ $post->scheduled_time->format('Y-m-d H:i') }}</p>
                    </div>

                    <div class="mb-3">
                        <h6 class="fw-bold">Status</h6>
                        <p>
                            <span class="badge
                                {{ $post->status == 'published' ? 'bg-success' :
                                   ($post->status == 'scheduled' ? 'bg-dark' :
                                   ($post->status == 'draft' ? 'bg-secondary' : 'bg-danger')) }}">
                                {{ ucfirst($post->status) }}
                            </span>
                        </p>
                    </div>

                    <div class="mb-3">
                        <h6 class="fw-bold">Platforms</h6>
                        <ul class="list-group list-group-flush">
                            @foreach($post->platforms as $platform)
                                <li class="list-group-item d-flex align-items-center">
                                    <i class="fab fa-{{ $platform->type }} fa-lg me-2"></i>
                                    {{ $platform->name }}
                                    <span class="ms-auto text-muted">
                                        (Status: {{ ucfirst($platform->pivot->platform_status) }})
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="mb-3">
                        <h6 class="fw-bold">Created By</h6>
                        <p>{{ $userName }} <span class="text-muted">({{ '@'.$emailHandle }})</span></p>
                    </div>

                    <div class="d-flex justify-content-end mt-3">
                        <a href="{{ route('dashboard') }}" class="btn btn-outline-dark">
                            <i class="fas fa-arrow-left me-1"></i>Back to Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection