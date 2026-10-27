<?php

namespace App\Services\Ad;

use App\Enums\AdSlot;
use App\Models\Ad;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class AdService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data, UploadedFile $image): Ad
    {
        $data['image_path'] = $image->store('ads', 'uploads');
        $data['sort_order'] = (int) Ad::query()
            ->where('slot', $data['slot'] ?? AdSlot::Left)
            ->max('sort_order') + 1;

        return Ad::query()->create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Ad $ad, array $data, ?UploadedFile $image = null): Ad
    {
        if ($image !== null) {
            $this->deleteImage($ad->image_path);
            $data['image_path'] = $image->store('ads', 'uploads');
        }

        $ad->update($data);

        return $ad->fresh() ?? $ad;
    }

    public function delete(Ad $ad): void
    {
        $this->deleteImage($ad->image_path);
        $ad->delete();
    }

    private function deleteImage(?string $path): void
    {
        if ($path === null || $path === '') {
            return;
        }

        Storage::disk('uploads')->delete($path);
    }
}
