<?php

namespace App\Http\Resources\Debate;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class DebateResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'category' => $this->category,
            'author' => $this->whenLoaded('author', fn () => [
                'id' => $this->author->id,
                'nick' => $this->author->nick,
            ]),
            'status' => $this->status instanceof \BackedEnum ? $this->status->value : $this->status,
            'image_url' => $this->image_path
                ? Storage::disk('public')->url($this->image_path)
                : null,
            'image_credit' => $this->image_credit,
            'video_file_url' => $this->video_path
                ? Storage::disk('public')->url($this->video_path)
                : null,
            'video_url' => $this->video_url,
            'sources' => $this->sources ?? [],
            'messages_count' => $this->whenCounted('messages'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
