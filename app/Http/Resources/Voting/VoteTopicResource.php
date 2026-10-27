<?php

namespace App\Http\Resources\Voting;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VoteTopicResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $target = (int) $this->target_signatures;
        $count = (int) $this->signatures_count;

        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'author' => $this->whenLoaded('author', fn () => [
                'id' => $this->author->id,
                'nick' => $this->author->nick,
            ]),
            'target_signatures' => $target,
            'signatures_count' => $count,
            'progress' => $target > 0 ? min(1, round($count / $target, 4)) : null,
            'deadline' => $this->deadline,
            'status' => $this->status instanceof \BackedEnum ? $this->status->value : $this->status,
            'created_at' => $this->created_at,
        ];
    }
}
