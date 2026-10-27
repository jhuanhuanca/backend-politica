<?php

namespace App\Services\Debate;

use App\Models\Debate;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class DebateService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data, int $authorId, ?UploadedFile $image = null, ?UploadedFile $video = null): Debate
    {
        $data['author_id'] = $authorId;
        $data['image_path'] = $this->storePublicFile($image, 'debates/images');
        $data['video_path'] = $this->storePublicFile($video, 'debates/videos');

        return Debate::query()->create($data)->load('author:id,nick');
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Debate $debate, array $data, ?UploadedFile $image = null, ?UploadedFile $video = null): Debate
    {
        if ($image !== null) {
            $this->deletePublicFile($debate->image_path);
            $data['image_path'] = $this->storePublicFile($image, 'debates/images');
        }

        if ($video !== null) {
            $this->deletePublicFile($debate->video_path);
            $data['video_path'] = $this->storePublicFile($video, 'debates/videos');
        }

        $debate->update($data);

        return $debate->fresh(['author:id,nick']) ?? $debate;
    }

    public function delete(Debate $debate): void
    {
        $this->deletePublicFile($debate->image_path);
        $this->deletePublicFile($debate->video_path);
        $debate->delete();
    }

    private function storePublicFile(?UploadedFile $file, string $directory): ?string
    {
        if ($file === null) {
            return null;
        }

        return $file->store($directory, 'public');
    }

    private function deletePublicFile(?string $path): void
    {
        if ($path === null || $path === '') {
            return;
        }

        Storage::disk('public')->delete($path);
    }
}
