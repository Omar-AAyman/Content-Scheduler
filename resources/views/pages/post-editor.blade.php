@extends('layouts.app')

@section('title', isset($post) ? 'Update Post - Content Scheduler' : 'Create New Post - Content Scheduler')
@section('header_title', isset($post) ? 'Update Post' : 'Create New Post')

@section('content')
<form id="postForm" method="POST" action="{{ isset($post) ? route('posts.update', $post) : route('posts.store') }}">
    @csrf
    @if(isset($post))
    @method('PUT')
    @endif

    <div class="container">
        <div class="row">
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0">Post Content</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="title" class="form-label">Title</label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title', $post->title ?? '') }}" required>
                            @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="content" class="form-label">Content</label>
                            <textarea class="form-control @error('content') is-invalid @enderror" id="content" name="content" rows="5" required>{{ old('content', $post->content ?? '') }}</textarea>
                            @error('content')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="d-flex justify-content-end mt-1">
                                <div class="char-counter text-muted">
                                    <span id="charCount">0</span><span id="charLimit">/280</span>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="image_url" class="form-label">Image URL (optional)</label>
                            <div class="input-group">
                                <input type="url" class="form-control @error('image_url') is-invalid @enderror" id="image_url" name="image_url" value="{{ old('image_url', $post->image_url ?? '') }}">
                                <button class="btn btn-outline-dark" type="button" id="previewImageBtn">Preview</button>
                                @error('image_url')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div id="imagePreviewContainer" class="mb-3 {{ old('image_url', $post->image_url ?? '') ? '' : 'd-none' }}">
                            <img id="imagePreview" src="{{ old('image_url', $post->image_url ?? '') }}" class="img-fluid rounded" alt="Preview">
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0">Platform Preview</h5>
                    </div>
                    <div class="card-body">
                        <ul class="nav nav-tabs" id="previewTabs" role="tablist">
                            @foreach($platforms as $index => $platform)
                            <li class="nav-item" role="presentation">
                                <button class="nav-link {{ $index === 0 ? 'active' : '' }}" id="{{ $platform->type }}-tab" data-bs-toggle="tab" data-bs-target="#{{ $platform->type }}" type="button" role="tab" aria-controls="{{ $platform->type }}" aria-selected="{{ $index === 0 ? 'true' : 'false' }}">
                                    <i class="fab fa-{{ $platform->type }} me-1"></i>{{ $platform->name }}
                                </button>
                            </li>
                            @endforeach
                        </ul>
                        <div class="tab-content p-3 border border-top-0 rounded-bottom" id="previewTabsContent">
                            @foreach($platforms as $index => $platform)
                            <div class="tab-pane fade {{ $index === 0 ? 'show active' : '' }}" id="{{ $platform->type }}" role="tabpanel" aria-labelledby="{{ $platform->type }}-tab">
                                @if($platform->type === 'twitter')
                                <div class="twitter-preview p-3 border rounded bg-light">
                                    <div>
                                        <div class="me-2">
                                            <img src="{{ asset('images/omar ayman logo.png') }}" alt="Logo" style="width: 48px; height: 48px; border-radius: 50%; object-fit: cover;">
                                        </div>
                                        <div>
                                            <div class="fw-bold">{{ $userName ?? 'Your Name' }}</div>
                                            <div class="text-muted">{{ $emailHandle ? '@'. $emailHandle : '@yourhandle' }}</div>
                                            <div class="mt-2" id="{{ $platform->type }}-preview-content">Your post will appear here...</div>
                                            <div id="{{ $platform->type }}-preview-image" class="mt-2 d-none">
                                                <img src="" class="img-fluid rounded" alt="{{ $platform->name }} image">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @elseif($platform->type === 'instagram')
                                <div class="instagram-preview p-3 border rounded bg-light">
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="me-2">
                                            <img src="{{ asset('images/omar ayman logo.png') }}" alt="Logo" style="width: 48px; height: 48px; border-radius: 50%; object-fit: cover;">
                                        </div>
                                        <div class="fw-bold">{{ $emailHandle ?? 'yourhandle' }}</div>
                                    </div>
                                    <div id="{{ $platform->type }}-preview-image" class="mb-2">
                                        <div class="placeholder-image text-center p-5 bg-white rounded border">
                                            <i class="fas fa-image fa-3x text-muted"></i>
                                            <p class="text-muted mt-2">Image will appear here...</p>
                                        </div>
                                    </div>
                                    <div id="{{ $platform->type }}-preview-content" class="mt-2">
                                        Your post will appear here...
                                    </div>
                                </div>
                                @elseif($platform->type === 'linkedin')
                                <div class="linkedin-preview p-3 border rounded bg-light">
                                    <div class="d-flex mb-3">
                                        <div class="me-2">
                                            <img src="{{ asset('images/omar ayman logo.png') }}" alt="Logo" style="width: 48px; height: 48px; border-radius: 50%; object-fit: cover;">
                                        </div>
                                        <div>
                                            <div class="fw-bold">{{ $userName ?? 'Your Name' }}</div>
                                            <div class="text-muted">Your Title</div>
                                        </div>
                                    </div>
                                    <div id="{{ $platform->type }}-preview-content" class="mb-2">Your post will appear here...</div>
                                    <div id="{{ $platform->type }}-preview-image" class="mt-2 d-none">
                                        <img src="" class="img-fluid rounded" alt="{{ $platform->name }} image">
                                    </div>
                                </div>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0">Publishing Options</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Select Platforms</label>
                            <div class="platform-selector d-flex flex-wrap gap-2">
                                @foreach($platforms ?? [] as $platform)
                                <div class="platform-badge p-2 border rounded {{ isset($post) && $post->platforms->contains($platform->id) ? 'bg-dark text-white' : 'bg-light' }}" data-platform="{{ $platform->type }}">
                                    <i class="fab fa-{{ $platform->type }} me-1"></i>{{ $platform->name }}
                                    <input type="hidden" name="platforms[]" id="platform_{{ $platform->type }}" value="{{ $platform->id }}" {{ isset($post) && $post->platforms->contains($platform->id) ? '' : 'disabled' }}>
                                </div>
                                @endforeach
                            </div>
                            <div id="platformError" class="text-danger mt-1 {{ $errors->has('platforms') ? '' : 'd-none' }}">
                                Please select at least one platform
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="scheduled_time" class="form-label">Schedule Time</label>
                            <input type="datetime-local" class="form-control @error('scheduled_time') is-invalid @enderror" id="scheduled_time" name="scheduled_time" value="{{ old('scheduled_time', isset($post) ? $post->scheduled_time->format('Y-m-d\TH:i') : '') }}" {{ isset($post) && $post->status === 'published' ? 'disabled' : '' }} {{ isset($post) ? '' : 'required' }}>
                            @error('scheduled_time')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary" {{ isset($post) && $post->status === 'published' ? 'disabled' : '' }}>
                                <i class="fas fa-calendar-check me-1"></i>
                                {{ isset($post) && $post->status === 'draft' ? 'Schedule Draft' : (isset($post) ? 'Update Post' : 'Schedule Post') }}
                            </button>
                            <button type="button" id="saveAsDraftBtn" class="btn btn-outline-dark">
                                <i class="fas fa-save me-1"></i>Save as Draft
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
@section('scripts')
<script>
    window.platformLimits = {
        @foreach($platforms as $platform)
            '{{ $platform->type }}': {{ $platform->max_content_length ?? 280 }}@if(!$loop->last),@endif
        @endforeach
    };
</script>
@endsection

