<?php

namespace App\Http\Requests\Suggestion;

use App\Enums\SuggestionType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSuggestionRequest extends FormRequest
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
            'type' => ['required', Rule::enum(SuggestionType::class)],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:5000'],
            'category' => ['nullable', 'string', 'max:100'],
            'target_signatures' => ['nullable', 'integer', 'min:10', 'max:10000000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'type.required' => 'Indicá si sugerís un debate o una votación.',
            'title.required' => 'El título es obligatorio.',
            'description.required' => 'Contá de qué se trata la propuesta.',
        ];
    }
}
