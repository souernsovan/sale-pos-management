<?php

namespace Tests\Feature;

use Tests\TestCase;

class HealthCheckTest extends TestCase
{
    public function test_health_check_returns_ok_without_authentication(): void
    {
        $this->getJson('/health')
            ->assertOk()
            ->assertJson(['status' => 'ok']);
    }
}
