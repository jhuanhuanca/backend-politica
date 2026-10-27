<?php

namespace App\Http\Requests\Admin;

use App\Enums\AdSlot;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAdRequest extends FormRequest
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
            'slot' => ['required', 'string', Rule::in(AdSlot::values())],
            'title' => ['nullable', 'string', 'max:120'],
            'link_url' => ['nullable', 'url', 'max:500'],
            'image' => ['required', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
