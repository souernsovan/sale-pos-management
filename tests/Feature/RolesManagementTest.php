<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RolesManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_create_a_role_with_a_subset_of_permissions(): void
    {
        $owner = User::factory()->create();

        $this->actingAs($owner)->post(route('roles.store'), [
            'name' => 'Stock Clerk',
            'permissions' => ['view products', 'manage products'],
        ])->assertRedirect(route('roles.index'));

        $role = Role::where('name', 'Stock Clerk')->firstOrFail();
        $this->assertTrue($role->hasPermissionTo('manage products'));
        $this->assertFalse($role->hasPermissionTo('view sales'));

        $this->actingAs($owner)->get(route('roles.index'))->assertOk()->assertSee('Stock Clerk');
    }

    public function test_role_can_be_edited_and_deleted(): void
    {
        $owner = User::factory()->create();
        $role = Role::create(['name' => 'Temp Role']);
        $role->syncPermissions(['view products']);

        $this->actingAs($owner)->put(route('roles.update', $role), [
            'name' => 'Temp Role',
            'permissions' => ['view products', 'manage products'],
        ])->assertRedirect(route('roles.index'));

        $this->assertTrue($role->fresh()->hasPermissionTo('manage products'));

        $this->actingAs($owner)->delete(route('roles.destroy', $role))->assertRedirect(route('roles.index'));
        $this->assertNull(Role::find($role->id));
    }

    public function test_reserved_role_names_cannot_be_created(): void
    {
        $owner = User::factory()->create();

        $this->actingAs($owner)->post(route('roles.store'), [
            'name' => 'Super Admin',
            'permissions' => [],
        ])->assertSessionHasErrors('name');
    }

    public function test_super_admin_role_cannot_be_edited_or_deleted(): void
    {
        $owner = User::factory()->create();
        $superAdmin = Role::where('name', 'Super Admin')->firstOrFail();

        $this->actingAs($owner)->get(route('roles.edit', $superAdmin))->assertStatus(422);
        $this->actingAs($owner)->delete(route('roles.destroy', $superAdmin))->assertStatus(422);
    }

    public function test_role_with_assigned_users_cannot_be_deleted(): void
    {
        $owner = User::factory()->create();
        $cashier = Role::where('name', 'Cashier')->firstOrFail();
        User::factory()->create()->syncRoles(['Cashier']);

        $this->actingAs($owner)->delete(route('roles.destroy', $cashier))
            ->assertSessionHasErrors('role');
    }

    public function test_create_edit_and_delete_are_independent_permissions(): void
    {
        $owner = User::factory()->create();

        $this->actingAs($owner)->post(route('roles.store'), [
            'name' => 'Editor Only',
            'permissions' => ['view products', 'edit products'],
        ])->assertRedirect(route('roles.index'));

        $role = Role::where('name', 'Editor Only')->firstOrFail();
        $this->assertTrue($role->hasPermissionTo('edit products'));
        $this->assertFalse($role->hasPermissionTo('create products'));
        $this->assertFalse($role->hasPermissionTo('delete products'));
    }

    public function test_cashier_can_be_reset_to_its_default_permissions(): void
    {
        $owner = User::factory()->create();
        $cashier = Role::where('name', 'Cashier')->firstOrFail();
        $cashier->givePermissionTo('manage settings');

        $this->actingAs($owner)->post(route('roles.reset-default', $cashier))
            ->assertRedirect(route('roles.edit', $cashier));

        $cashier->refresh();
        $this->assertFalse($cashier->hasPermissionTo('manage settings'));
        $this->assertTrue($cashier->hasPermissionTo('access pos'));
    }

    public function test_a_custom_role_has_no_default_to_reset_to(): void
    {
        $owner = User::factory()->create();
        $role = Role::create(['name' => 'Custom Role']);

        $this->actingAs($owner)->post(route('roles.reset-default', $role))->assertStatus(422);
    }
}
