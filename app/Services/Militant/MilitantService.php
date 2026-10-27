<?php

namespace App\Services\Militant;

use App\Contracts\MilitantRegistry;
use App\Enums\MilitantStatus;
use App\Models\Militant;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Validation\ValidationException;

class MilitantService implements MilitantRegistry
{
    public function register(array $payload, ?int $userId = null): void
    {
        try {
            Militant::query()->create([
                'user_id' => $userId,
                'full_name' => $payload['full_name'],
                'ci' => $this->normalizeCi($payload['ci']),
                'email' => strtolower($payload['email']),
                'phone' => $this->normalizePhone($payload['phone']),
                'city' => $payload['city'],
                'department' => $payload['department'],
                'birth_year' => $payload['birth_year'],
                'consent' => $payload['consent'],
                'status' => MilitantStatus::Pending,
            ]);
        } catch (UniqueConstraintViolationException|QueryException $exception) {
            if ($this->isUniqueViolation($exception)) {
                throw ValidationException::withMessages([
                    'ci' => ['Ya existe un registro con esa CI o ese email.'],
                ]);
            }

            throw $exception;
        }
    }

    public function updateStatus(Militant $militant, MilitantStatus $status): Militant
    {
        $militant->update(['status' => $status]);

        return $militant->fresh() ?? $militant;
    }

    private function normalizeCi(string $ci): string
    {
        return strtoupper((string) preg_replace('/\s+/', '', $ci));
    }

    private function normalizePhone(string $phone): string
    {
        return (string) preg_replace('/[^\d+]/', '', $phone);
    }

    private function isUniqueViolation(QueryException $exception): bool
    {
        $sqlState = $exception->errorInfo[0] ?? null;

        return $exception instanceof UniqueConstraintViolationException
            || $sqlState === '23000'
            || str_contains($exception->getMessage(), 'UNIQUE constraint failed');
    }
}
