<?php

namespace App\Http\Resources\Voting;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SignatureResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'vote_topic_id' => $this->vote_topic_id,
            'vote_topic' => $this->whenLoaded('voteTopic', fn () => [
                'id' => $this->voteTopic->id,
                'title' => $this->voteTopic->title,
                'target_signatures' => $this->voteTopic->target_signatures,
                'signatures_count' => $this->voteTopic->signatures_count,
            ]),
            'created_at' => $this->created_at,
        ];
    }
}
