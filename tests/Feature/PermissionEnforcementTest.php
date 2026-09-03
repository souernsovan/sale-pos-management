<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use App\Livewire\Pos\Terminal;
use Tests\TestCase;

class PermissionEnforcementTest extends TestCase
{
    use RefreshDatabase;

    private function cashier(): User
    {
        $user = User::factory()->create();
        $user->syncRoles(['Cashier']);

        return $user;
    }

    public function test_cashier_can_reach_pos_and_complete_a_sale(): void
    {
        $cashier = $this->cashier();
        $product = Product::create([
            'name' => 'Gum', 'sku' => 'SKU-500', 'barcode' => 'BC-500',
            'price' => 1.00, 'cost' => 0.40, 'stock_qty' => 10,
        ]);

        $this->actingAs($cashier)->get(route('pos.index'))->assertOk();

        Livewire::actingAs($cashier)->test(Terminal::class)
            ->set('barcode', 'BC-500')->call('scan')
            ->call('checkout')
            ->assertRedirect();

        $this->assertSame(9, $product->fresh()->stock_qty);
    }

    public function test_cashier_is_blocked_from_settings_reports_and_categories(): void
    {
        $cashier = $this->cashier();

        $this->actingAs($cashier)->get(route('settings.edit'))->assertForbidden();
        $this->actingAs($cashier)->get(route('reports.index'))->assertForbidden();
        $this->actingAs($cashier)->get(route('categories.index'))->assertForbidden();
        $this->actingAs($cashier)->get(route('roles.index'))->assertForbidden();
    }

    public function test_cashier_can_view_but_not_manage_products(): void
    {
        $cashier = $this->cashier();
        $product = Product::create([
            'name' => 'Candy', 'sku' => 'SKU-501', 'price' => 0.50, 'cost' => 0.20, 'stock_qty' => 5,
        ]);

        $this->actingAs($cashier)->get(route('products.index'))->assertOk();
        $this->actingAs($cashier)->get(route('products.create'))->assertForbidden();
        $this->actingAs($cashier)->post(route('products.store'), [
            'name' => 'New', 'sku' => 'SKU-502', 'price' => 1,
        ])->assertForbidden();
        $this->actingAs($cashier)->delete(route('products.destroy', $product))->assertForbidden();
    }

    public function test_livewire_pos_component_rejects_users_without_access_pos_permission(): void
    {
        $viewer = User::factory()->create();
        $viewer->syncRoles([]); // no permissions at all

        Livewire::actingAs($viewer)->test(Terminal::class)->assertStatus(403);
    }
}
