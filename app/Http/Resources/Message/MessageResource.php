<?php

namespace App\Http\Resources\Message;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => (int) $this->id,
            'debate_id' => (int) $this->debate_id,
            'user' => $this->whenLoaded('user', fn () => [
                'id' => $this->user->id,
                'nick' => $this->user->nick,
            ]),
            'content' => $this->content,
            'parent_id' => $this->parent_id ? (int) $this->parent_id : null,
            'created_at' => $this->created_at,
        ];
    }
}
