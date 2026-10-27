<?php

namespace App\Models;

use App\Enums\VoteTopicStatus;
use Database\Factories\VoteTopicFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VoteTopic extends Model
{
    /** @use HasFactory<VoteTopicFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'description',
        'author_id',
        'target_signatures',
        'signatures_count',
        'deadline',
        'status',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'deadline' => 'datetime',
            'target_signatures' => 'integer',
            'signatures_count' => 'integer',
            'status' => VoteTopicStatus::class,
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function signatures(): HasMany
    {
        return $this->hasMany(Signature::class);
    }

    public function isOpenForSignatures(): bool
    {
        if ($this->status !== VoteTopicStatus::Open) {
            return false;
        }

        return $this->deadline === null || $this->deadline->isFuture();
    }
}
