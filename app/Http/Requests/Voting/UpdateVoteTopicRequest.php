<?php

namespace App\Http\Requests\Voting;

use App\Models\VoteTopic;
use Illuminate\Foundation\Http\FormRequest;

class UpdateVoteTopicRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var VoteTopic $topic */
        $topic = $this->route('voteTopic');

        return $this->user()?->isAdmin() ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'target_signatures' => ['sometimes', 'integer', 'min:0', 'max:10000000'],
            'deadline' => ['nullable', 'date'],
            'status' => ['sometimes', 'in:open,closed,achieved'],
        ];
    }
}
