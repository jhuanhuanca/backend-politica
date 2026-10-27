<?php

namespace App\Services\Suggestion;

use App\Enums\SuggestionStatus;
use App\Enums\SuggestionType;
use App\Models\Suggestion;
use App\Models\User;
use App\Services\Debate\DebateService;
use App\Services\Voting\VoteTopicService;
use Illuminate\Validation\ValidationException;

class SuggestionService
{
    public function __construct(
        private readonly DebateService $debates,
        private readonly VoteTopicService $votes,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data, User $user): Suggestion
    {
        $data['user_id'] = $user->id;
        $data['status'] = SuggestionStatus::Pending;

        return Suggestion::query()->create($data)->load('user:id,nick');
    }

    public function reject(Suggestion $suggestion, ?string $note = null): Suggestion
    {
        if (! $suggestion->isPending()) {
            throw ValidationException::withMessages([
                'status' => ['Solo se puede rechazar una sugerencia pendiente.'],
            ]);
        }

        $suggestion->update([
            'status' => SuggestionStatus::Rejected,
            'admin_note' => $note,
        ]);

        return $suggestion->fresh(['user:id,nick']) ?? $suggestion;
    }

    public function publish(Suggestion $suggestion): Suggestion
    {
        if (! $suggestion->isPending()) {
            throw ValidationException::withMessages([
                'status' => ['Esta sugerencia ya fue revisada.'],
            ]);
        }

        $suggestion->loadMissing('user');
        $author = $suggestion->user;

        if ($author === null) {
            throw ValidationException::withMessages([
                'user_id' => ['La cuenta que sugirió este tema ya no existe.'],
            ]);
        }

        if ($suggestion->type === SuggestionType::Debate) {
            $debate = $this->debates->create([
                'title' => $suggestion->title,
                'description' => $suggestion->description,
                'category' => $suggestion->category,
            ], $author->id);

            $suggestion->debate_id = $debate->id;
        } else {
            $topic = $this->votes->create([
                'title' => $suggestion->title,
                'description' => $suggestion->description,
                'target_signatures' => $suggestion->target_signatures ?: 1000,
            ], $author);

            $suggestion->vote_topic_id = $topic->id;
        }

        $suggestion->status = SuggestionStatus::Accepted;
        $suggestion->save();

        return $suggestion->fresh(['user:id,nick', 'debate:id,title', 'voteTopic:id,title']) ?? $suggestion;
    }
}
