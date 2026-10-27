<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Signature extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'vote_topic_id',
        'user_id',
        'email_hash',
        'ci_hash',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'email_hash',
        'ci_hash',
    ];

    public function voteTopic(): BelongsTo
    {
        return $this->belongsTo(VoteTopic::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
