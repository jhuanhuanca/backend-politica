<?php

namespace App\Policies;

use App\Models\User;
use App\Models\VoteTopic;

class VoteTopicPolicy
{
    public function update(User $user, VoteTopic $voteTopic): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, VoteTopic $voteTopic): bool
    {
        return $this->update($user, $voteTopic);
    }
}
