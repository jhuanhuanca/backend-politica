<?php

namespace App\Http\Requests\Auth;

use App\Support\Bolivia;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'nick' => ['required', 'string', 'max:40', 'unique:users,nick'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', Password::min(8)],
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
