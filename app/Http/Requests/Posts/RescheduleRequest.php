<?php

namespace App\Http\Requests\Posts;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class RescheduleRequest extends FormRequest
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
        return [
            'scheduled_time' => ['required', 'date', 'after:now'],
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'scheduled_time.required' => 'The scheduled time is required.',
            'scheduled_time.date' => 'The scheduled time must be a valid date.',
            'scheduled_time.after' => 'The scheduled time must be in the future.',
        ];
    }

    /**
     * Validate post status after base validation.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $post = $this->route('post');
            if (!in_array($post->status, ['draft', 'scheduled', 'failed'])) {
                $validator->errors()->add(
                    'status',
                    'Only draft, scheduled, or failed posts can be rescheduled.'
                );
            }
        });
    }
}
