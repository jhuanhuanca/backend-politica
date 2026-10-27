<?php

namespace Tests\Unit;

use App\Support\IdentityHasher;
use Tests\TestCase;

class IdentityHasherTest extends TestCase
{
    public function test_email_hash_is_stable_and_case_insensitive(): void
    {
        $hasher = new IdentityHasher;

        $this->assertSame(
            $hasher->hashEmail('Ada@Example.com'),
            $hasher->hashEmail('  ada@example.com  ')
        );
        $this->assertNotEquals(
            $hasher->hashEmail('ada@example.com'),
            $hasher->hashEmail('otra@example.com')
        );
    }

    public function test_ci_hash_ignores_spaces(): void
    {
        $hasher = new IdentityHasher;

        $this->assertSame(
            $hasher->hashCi('123 456'),
            $hasher->hashCi('123456')
        );
    }
}
