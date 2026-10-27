<?php

namespace App\Http\Requests\Message;

use App\Models\Debate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpKernel\Exception\HttpException;

class StoreMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Debate $debate */
        $debate = $this->route('debate');

        return $this->user() !== null && $debate->isOpen();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Debate $debate */
        $debate = $this->route('debate');

        return [
            'content' => ['required', 'string', 'max:5000'],
            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists('messages', 'id')->where('debate_id', $debate->id),
            ],
        ];
    }

    protected function failedAuthorization(): void
    {
        throw new HttpException(403, 'Este debate no admite mensajes.');
    }
}
