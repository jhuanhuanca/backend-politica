<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminMilitantResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'full_name' => $this->full_name,
            'ci' => $this->ci,
            'email' => $this->email,
            'phone' => $this->phone,
            'city' => $this->city,
            'department' => $this->department,
            'birth_year' => $this->birth_year,
            'status' => $this->status instanceof \BackedEnum ? $this->status->value : $this->status,
            'card_number' => $this->card_number,
            'created_at' => $this->created_at,
        ];
    }
}
