<?php

namespace App\Services\Voting;

use App\Enums\VoteTopicStatus;
use App\Models\Signature;
use App\Models\User;
use App\Models\VoteTopic;
use App\Support\IdentityHasher;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;

class SignatureService
{
    public function __construct(private readonly IdentityHasher $hasher) {}

    public function sign(VoteTopic $topic, User $user, ?string $ci = null): Signature
    {
        if (! $topic->isOpenForSignatures()) {
            throw new HttpException(403, 'Este tema no está abierto para firmas.');
        }

        $emailHash = $user->email_hash ?: $this->hasher->hashEmail($user->email);
        $ciHash = $ci !== null && $ci !== '' ? $this->hasher->hashCi($ci) : null;

        try {
            return DB::transaction(function () use ($topic, $user, $emailHash, $ciHash) {
                /** @var VoteTopic $locked */
                $locked = VoteTopic::query()
                    ->whereKey($topic->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if (! $locked->isOpenForSignatures()) {
                    throw new HttpException(403, 'Este tema no está abierto para firmas.');
                }

                $signature = Signature::query()->create([
                    'vote_topic_id' => $locked->id,
                    'user_id' => $user->id,
                    'email_hash' => $emailHash,
                    'ci_hash' => $ciHash,
                ]);

                $locked->increment('signatures_count');
                $locked->refresh();

                if (
                    $locked->target_signatures > 0
                    && $locked->signatures_count >= $locked->target_signatures
                ) {
                    $locked->update(['status' => VoteTopicStatus::Achieved]);
                }

                return $signature;
            });
        } catch (UniqueConstraintViolationException|QueryException $exception) {
            if ($this->isUniqueViolation($exception)) {
                throw new HttpException(422, 'Ya has firmado este tema.');
            }

            throw $exception;
        }
    }

    private function isUniqueViolation(QueryException $exception): bool
    {
        $sqlState = $exception->errorInfo[0] ?? null;

        return $exception instanceof UniqueConstraintViolationException
            || $sqlState === '23000'
            || str_contains($exception->getMessage(), 'UNIQUE constraint failed');
    }
}
