<?php

namespace App\Services\Voting;

use App\Models\User;
use App\Models\VoteTopic;

class VoteTopicService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data, User $author): VoteTopic
    {
        $data['author_id'] = $author->id;

        return VoteTopic::query()->create($data)->load('author:id,nick');
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(VoteTopic $topic, array $data): VoteTopic
    {
        $topic->update($data);

        return $topic->fresh(['author:id,nick']) ?? $topic;
    }

    public function delete(VoteTopic $topic): void
    {
        $topic->delete();
    }
}
