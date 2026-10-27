<?php

namespace App\Http\Requests\Admin;

use App\Enums\AdSlot;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAdRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() === true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'slot' => ['sometimes', 'string', Rule::in(AdSlot::values())],
            'title' => ['nullable', 'string', 'max:120'],
            'link_url' => ['nullable', 'url', 'max:500'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
