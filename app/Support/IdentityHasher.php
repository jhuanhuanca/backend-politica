<?php

namespace App\Support;

final class IdentityHasher
{
    public function hashEmail(string $email): string
    {
        return $this->hmac(mb_strtolower(trim($email)));
    }

    public function hashCi(string $ci): string
    {
        $normalized = preg_replace('/\s+/', '', trim($ci)) ?? '';

        return $this->hmac($normalized);
    }

    private function hmac(string $value): string
    {
        return hash_hmac('sha256', $value, $this->secret());
    }

    private function secret(): string
    {
        $key = (string) config('identity.hash_key', config('app.key'));

        if (str_starts_with($key, 'base64:')) {
            $decoded = base64_decode(substr($key, 7), true);

            return $decoded !== false ? $decoded : $key;
        }

        return $key;
    }
}
