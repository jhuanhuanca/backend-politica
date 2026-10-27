<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminUserResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nick' => $this->nick,
            'name' => $this->name,
            'email' => $this->email,
            'department' => $this->department,
            'is_admin' => (bool) $this->is_admin,
            'messages_count' => $this->whenCounted('messages'),
            'signatures_count' => $this->whenCounted('signatures'),
            'debates_count' => $this->whenCounted('debates'),
            'suggestions_count' => $this->whenCounted('suggestions'),
            'created_at' => $this->created_at,
        ];
    }
}
