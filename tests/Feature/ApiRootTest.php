<?php

namespace Tests\Feature;

use Tests\TestCase;

class ApiRootTest extends TestCase
{
    public function test_root_returns_json_ok(): void
    {
        $this->getJson('/')
            ->assertOk()
            ->assertJson([
                'name' => config('app.name'),
                'status' => 'ok',
            ]);
    }

    public function test_health_endpoint_is_up(): void
    {
        $this->get('/up')->assertOk();
    }
}
