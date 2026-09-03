<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_update_shop_settings(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->put(route('settings.update'), [
            'shop_name' => 'My Home Shop',
            'shop_address' => '123 Main St',
            'currency_symbol' => '$',
            'tax_rate' => 7.5,
            'low_stock_threshold' => 10,
        ])->assertRedirect(route('settings.edit'));

        $this->assertSame('My Home Shop', Setting::get('shop_name'));
        $this->assertSame('10', Setting::get('low_stock_threshold'));

        $this->actingAs($user)->get(route('settings.edit'))->assertOk()->assertSee('My Home Shop');
    }

    public function test_owner_can_create_edit_and_deactivate_a_staff_user(): void
    {
        $owner = User::factory()->create();

        $this->actingAs($owner)->post(route('settings.users.store'), [
            'name' => 'Staff One',
            'email' => 'staff@example.com',
            'password' => 'password123',
            'role' => 'Cashier',
        ])->assertRedirect(route('settings.edit'));

        $staff = User::where('email', 'staff@example.com')->firstOrFail();
        $this->assertTrue($staff->is_active);
        $this->assertTrue($staff->hasRole('Cashier'));

        $this->actingAs($owner)->post(route('settings.users.toggle-active', $staff))->assertRedirect();
        $this->assertFalse($staff->fresh()->is_active);

        $this->post(route('logout'));

        $this->post(route('login'), [
            'email' => 'staff@example.com',
            'password' => 'password123',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_owner_cannot_deactivate_or_delete_their_own_account(): void
    {
        $owner = User::factory()->create();

        $this->actingAs($owner)->post(route('settings.users.toggle-active', $owner))->assertStatus(422);
        $this->actingAs($owner)->delete(route('settings.users.destroy', $owner))->assertStatus(422);
    }
}
