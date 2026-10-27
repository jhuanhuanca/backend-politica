<?php

namespace App\Models;

use App\Enums\DebateStatus;
use Database\Factories\DebateFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Debate extends Model
{
    /** @use HasFactory<DebateFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'description',
        'category',
        'author_id',
        'status',
            'image_path',
            'image_credit',
            'video_path',
            'video_url',
            'sources',
        ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => DebateStatus::class,
            'sources' => 'array',
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function isOpen(): bool
    {
        return $this->status === DebateStatus::Open;
    }
}
