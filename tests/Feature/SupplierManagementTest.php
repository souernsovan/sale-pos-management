<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupplierManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_create_edit_and_delete_a_supplier(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('suppliers.store'), [
            'name' => 'Acme Distributors',
            'phone' => '555-1234',
            'address' => '1 Market St',
            'notes' => 'Delivers on Mondays',
        ])->assertRedirect(route('suppliers.index'));

        $supplier = Supplier::firstOrFail();
        $this->assertSame('Acme Distributors', $supplier->name);

        $this->actingAs($user)->get(route('suppliers.index'))->assertOk()->assertSee('Acme Distributors');
        $this->actingAs($user)->get(route('suppliers.edit', $supplier))->assertOk();

        $this->actingAs($user)->put(route('suppliers.update', $supplier), [
            'name' => 'Acme Distributors Ltd',
            'phone' => '555-1234',
        ])->assertRedirect(route('suppliers.index'));

        $this->assertSame('Acme Distributors Ltd', $supplier->fresh()->name);

        $this->actingAs($user)->delete(route('suppliers.destroy', $supplier))->assertRedirect(route('suppliers.index'));
        $this->assertSoftDeleted($supplier);
    }

    public function test_a_soft_deleted_supplier_still_appears_on_products_that_reference_it(): void
    {
        $user = User::factory()->create();
        $supplier = Supplier::create(['name' => 'Global Foods']);
        $product = Product::create([
            'name' => 'Rice', 'sku' => 'SKU-700', 'price' => 5, 'cost' => 3, 'stock_qty' => 10,
            'supplier_id' => $supplier->id,
        ]);

        $supplier->delete();

        $this->assertSame('Global Foods', $product->fresh()->supplier?->name);
        $this->actingAs($user)->get(route('products.show', $product))->assertOk()->assertSee('Global Foods');
    }
}
