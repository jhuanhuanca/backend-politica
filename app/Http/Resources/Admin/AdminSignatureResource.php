<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminSignatureResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'vote_topic_id' => $this->vote_topic_id,
            'user' => $this->whenLoaded('user', fn () => [
                'id' => $this->user?->id,
                'nick' => $this->user?->nick,
                'email' => $this->user?->email,
            ]),
            'created_at' => $this->created_at,
        ];
    }
}
