<?php

namespace App\Http\Requests\Posts;

use App\Models\Platform;
use App\Models\Post;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check() && $this->user()->can('update', $this->route('post'));
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $activePlatformIds = Auth::user()->platforms()->wherePivot('is_active', true)->pluck('platforms.id')->toArray();

        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'image_url' => ['nullable', 'url'],
            'scheduled_time' => ['required', 'date', 'after:now'],
            'platforms' => ['required', 'array', 'min:1'],
            'platforms.*' => ['exists:platforms,id', Rule::in($activePlatformIds)],
        ];

        // Platform-specific content length
        $platforms = Platform::whereIn('id', $this->input('platforms', []))
            ->whereIn('id', $activePlatformIds)
            ->get();
        foreach ($platforms as $platform) {
            if ($platform->max_content_length) {
                $rules["platform_content_{$platform->id}"] = ["nullable", "string", "max:{$platform->max_content_length}"];
            }
        }

        return $rules;
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'The post title is required.',
            'title.max' => 'The post title cannot exceed 255 characters.',
            'content.required' => 'The post content is required.',
            'image_url.url' => 'The image URL must be a valid URL.',
            'scheduled_time.required' => 'The scheduled time is required.',
            'scheduled_time.date' => 'The scheduled time must be a valid date.',
            'scheduled_time.after' => 'The scheduled time must be in the future.',
            'platforms.required' => 'At least one platform must be selected.',
            'platforms.*.exists' => 'One or more selected platforms are invalid.',
            'platforms.*.in' => 'One or more selected platforms are not active for your account.',
        ];
    }

    /**
     * Validate daily post limit after base validation.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $scheduledDate = date('Y-m-d', strtotime($this->scheduled_time));
            $post = $this->route('post');
            $dailyPostCount = Post::where('user_id', Auth::id())
                ->where('status', 'scheduled')
                ->whereDate('scheduled_time', $scheduledDate)
                ->where('id', '!=', $post->id)
                ->count();

            if ($dailyPostCount >= 10) {
                $validator->errors()->add(
                    'limit',
                    "You have reached the daily limit of 10 scheduled posts for $scheduledDate."
                );
            }

            if ($post->status === 'published') {
                $validator->errors()->add(
                    'status',
                    'Cannot edit a published post.'
                );
            }
        });
    }
}
