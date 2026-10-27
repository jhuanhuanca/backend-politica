<?php

namespace App\Policies;

use App\Models\Debate;
use App\Models\User;

class DebatePolicy
{
    public function update(User $user, Debate $debate): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Debate $debate): bool
    {
        return $this->update($user, $debate);
    }
}
