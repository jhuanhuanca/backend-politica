<?php

namespace App\Services\Debate;

use App\Events\Debate\MessageSent;
use App\Models\Debate;
use App\Models\Message;
use App\Models\User;
use Symfony\Component\HttpKernel\Exception\HttpException;

class MessageService
{
    public function create(Debate $debate, User $user, string $content, ?int $parentId = null): Message
    {
        if (! $debate->isOpen()) {
            throw new HttpException(403, 'Este debate no admite mensajes.');
        }

        $message = $debate->messages()->create([
            'user_id' => $user->id,
            'content' => $content,
            'parent_id' => $parentId,
        ]);

        $message->load('user:id,nick');

        try {
            broadcast(new MessageSent($message))->toOthers();
        } catch (\Throwable $e) {
            report($e);
        }

        return $message;
    }
}
