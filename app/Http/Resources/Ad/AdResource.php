<?php

namespace App\Http\Resources\Ad;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class AdResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slot' => $this->slot instanceof \BackedEnum ? $this->slot->value : $this->slot,
            'title' => $this->title,
            'image_url' => $this->image_path
                ? Storage::disk('uploads')->url($this->image_path)
                : null,
            'link_url' => $this->link_url,
            'is_active' => (bool) $this->is_active,
            'sort_order' => (int) $this->sort_order,
            'created_at' => $this->created_at,
        ];
    }
}
