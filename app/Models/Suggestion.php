<?php

namespace App\Models;

use App\Enums\SuggestionStatus;
use App\Enums\SuggestionType;
use Database\Factories\SuggestionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Suggestion extends Model
{
    /** @use HasFactory<SuggestionFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'type',
        'title',
        'description',
        'category',
        'target_signatures',
        'status',
        'admin_note',
        'debate_id',
        'vote_topic_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => SuggestionType::class,
            'status' => SuggestionStatus::class,
            'target_signatures' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function debate(): BelongsTo
    {
        return $this->belongsTo(Debate::class);
    }

    public function voteTopic(): BelongsTo
    {
        return $this->belongsTo(VoteTopic::class);
    }

    public function isPending(): bool
    {
        return $this->status === SuggestionStatus::Pending;
    }
}
