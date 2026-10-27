<?php

namespace App\Http\Requests\Auth;

use App\Support\Bolivia;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'department' => ['required', 'string', Rule::in(Bolivia::DEPARTMENTS)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'department.required' => 'Elegí tu departamento.',
            'department.in' => 'Elegí un departamento válido.',
        ];
    }
}
