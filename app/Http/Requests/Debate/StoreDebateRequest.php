<?php

namespace App\Http\Requests\Debate;

use Illuminate\Foundation\Http\FormRequest;

class StoreDebateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'category' => ['nullable', 'string', 'max:100'],
            'status' => ['sometimes', 'in:open,closed,moderated'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
            'image_credit' => ['nullable', 'string', 'max:255'],
            'video' => ['nullable', 'mimes:mp4,mov,avi,webm', 'max:51200'],
            'video_url' => ['nullable', 'url', 'max:500'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->input('video_url') === '') {
            $this->merge(['video_url' => null]);
        }
    }
}
