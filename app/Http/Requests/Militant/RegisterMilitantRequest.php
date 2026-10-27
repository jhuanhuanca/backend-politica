<?php

namespace App\Http\Requests\Militant;

use App\Support\Bolivia;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterMilitantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('ci')) {
            $this->merge([
                'ci' => strtoupper((string) preg_replace('/\s+/', '', (string) $this->input('ci'))),
            ]);
        }

        if ($this->has('email')) {
            $this->merge([
                'email' => strtolower(trim((string) $this->input('email'))),
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $maxYear = now()->year - 18;
        $minYear = now()->year - 100;

        return [
            'full_name' => ['required', 'string', 'min:5', 'max:120'],
            'ci' => ['required', 'string', 'min:5', 'max:20', 'unique:militancia.militants,ci'],
            'email' => ['required', 'email', 'max:255', 'unique:militancia.militants,email'],
            'phone' => ['required', 'string', 'min:8', 'max:20'],
            'city' => ['required', 'string', 'min:2', 'max:80'],
            'department' => ['required', 'string', Rule::in(Bolivia::DEPARTMENTS)],
            'birth_year' => ['required', 'integer', 'min:'.$minYear, 'max:'.$maxYear],
            'consent' => ['accepted'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'full_name' => 'nombre completo',
            'ci' => 'CI',
            'email' => 'email',
            'phone' => 'celular',
            'city' => 'ciudad',
            'department' => 'departamento',
            'birth_year' => 'año de nacimiento',
            'consent' => 'consentimiento',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'consent.accepted' => 'Tenés que aceptar el tratamiento de tus datos para el padrón.',
            'birth_year.min' => 'El año de nacimiento no es válido.',
            'birth_year.max' => 'Tenés que ser mayor de 18 años.',
            'ci.unique' => 'Esa CI ya está registrada en el padrón.',
            'email.unique' => 'Ese email ya está registrado en el padrón.',
        ];
    }
}
