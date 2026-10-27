<?php

namespace App\Http\Resources\Suggestion;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SuggestionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type instanceof \BackedEnum ? $this->type->value : $this->type,
            'title' => $this->title,
            'description' => $this->description,
            'category' => $this->category,
            'target_signatures' => $this->target_signatures,
            'status' => $this->status instanceof \BackedEnum ? $this->status->value : $this->status,
            'admin_note' => $this->admin_note,
            'debate_id' => $this->debate_id,
            'vote_topic_id' => $this->vote_topic_id,
            'user' => $this->whenLoaded('user', fn () => [
                'id' => $this->user->id,
                'nick' => $this->user->nick,
            ]),
            'created_at' => $this->created_at,
        ];
    }
}
