<?php

namespace App\Contracts;

interface MilitantRegistry
{
    /**
     * @param  array{
     *     full_name: string,
     *     ci: string,
     *     email: string,
     *     phone: string,
     *     city: string,
     *     department: string,
     *     birth_year: int,
     *     consent: bool
     * }  $payload
     */
    public function register(array $payload, ?int $userId = null): void;

    public function updateStatus(\App\Models\Militant $militant, \App\Enums\MilitantStatus $status): \App\Models\Militant;
}
