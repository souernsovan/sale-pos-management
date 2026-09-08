<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocalizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_renders_in_english_by_default(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('dashboard'))->assertOk()->assertSee('Dashboard');
    }

    public function test_switching_to_khmer_persists_across_requests(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('locale.update'), ['locale' => 'km'])->assertRedirect();

        $this->actingAs($user)->get(route('dashboard'))->assertOk()->assertSee('ផ្ទាំងគ្រប់គ្រង');

        // Switching back to English drops the Khmer text again.
        $this->actingAs($user)->post(route('locale.update'), ['locale' => 'en'])->assertRedirect();
        $this->actingAs($user)->get(route('dashboard'))->assertOk()->assertSee('Dashboard');
    }

    public function test_an_unsupported_locale_is_rejected(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('locale.update'), ['locale' => 'fr'])->assertSessionHasErrors('locale');
    }
}
