<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_create_edit_and_deactivate_a_staff_user(): void
    {
        $owner = User::factory()->create();

        $this->actingAs($owner)->get(route('users.index'))->assertOk();

        $this->actingAs($owner)->post(route('users.store'), [
            'name' => 'Staff One',
            'email' => 'staff@example.com',
            'password' => 'password123',
            'role' => 'Cashier',
        ])->assertRedirect(route('users.index'));

        $staff = User::where('email', 'staff@example.com')->firstOrFail();
        $this->assertTrue($staff->is_active);
        $this->assertTrue($staff->hasRole('Cashier'));

        $this->actingAs($owner)->get(route('users.index'))->assertOk()->assertSee('Staff One');
        $this->actingAs($owner)->get(route('users.edit', $staff))->assertOk();

        $this->actingAs($owner)->put(route('users.update', $staff), [
            'name' => 'Staff One Updated',
            'email' => 'staff@example.com',
            'role' => 'Cashier',
        ])->assertRedirect(route('users.index'));

        $this->assertSame('Staff One Updated', $staff->fresh()->name);

        $this->actingAs($owner)->post(route('users.toggle-active', $staff))->assertRedirect();
        $this->assertFalse($staff->fresh()->is_active);

        $this->post(route('logout'));

        $this->post(route('login'), [
            'email' => 'staff@example.com',
            'password' => 'password123',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_owner_can_delete_a_staff_user_with_no_sales_history(): void
    {
        $owner = User::factory()->create();
        $staff = User::factory()->create(['email' => 'todelete@example.com']);
        $staff->syncRoles(['Cashier']);

        $this->actingAs($owner)->delete(route('users.destroy', $staff))->assertRedirect(route('users.index'));
        $this->assertModelMissing($staff);
    }

    public function test_owner_cannot_deactivate_or_delete_their_own_account(): void
    {
        $owner = User::factory()->create();

        $this->actingAs($owner)->post(route('users.toggle-active', $owner))->assertStatus(422);
        $this->actingAs($owner)->delete(route('users.destroy', $owner))->assertStatus(422);
    }
}
