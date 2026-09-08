<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class DeployControllerTest extends TestCase
{
    /**
     * These tests hit real `config:cache`/`route:cache` commands, which
     * write real files to bootstrap/cache/ — clear them again afterward so
     * a cached route/config table from this test run doesn't leak into
     * later test runs or local `php artisan serve`.
     */
    protected function tearDown(): void
    {
        Artisan::call('config:clear');
        Artisan::call('route:clear');
        Artisan::call('view:clear');

        parent::tearDown();
    }

    public function test_optimize_requires_a_configured_token(): void
    {
        config(['services.deploy_token' => null]);

        $this->get(route('deploy.optimize'))->assertNotFound();
    }

    public function test_optimize_rejects_a_wrong_token(): void
    {
        config(['services.deploy_token' => 'correct-token']);

        $this->get(route('deploy.optimize', ['token' => 'wrong-token']))->assertForbidden();
    }

    public function test_optimize_runs_the_caching_commands_with_the_right_token(): void
    {
        config(['services.deploy_token' => 'correct-token']);

        $response = $this->get(route('deploy.optimize', ['token' => 'correct-token']));

        $response->assertOk();
        $response->assertSee('Optimized.', false);
        $response->assertSee('php artisan config:cache', false);
        $response->assertSee('php artisan route:cache', false);
        $response->assertSee('php artisan view:cache', false);
    }

    public function test_clear_runs_with_the_right_token(): void
    {
        config(['services.deploy_token' => 'correct-token']);

        $response = $this->get(route('deploy.clear', ['token' => 'correct-token']));

        $response->assertOk();
        $response->assertSee('Cleared.', false);
        $response->assertSee('php artisan config:clear', false);
    }
}
