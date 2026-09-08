<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_create_a_category_and_product_with_stock(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('categories.store'), ['name' => 'Snacks'])
            ->assertRedirect(route('categories.index'));

        $category = Category::firstOrFail();

        $response = $this->actingAs($user)->post(route('products.store'), [
            'category_id' => $category->id,
            'name' => 'Instant Noodles',
            'sku' => 'SKU-001',
            'price' => 1.50,
            'cost' => 1.00,
            'stock_qty' => 20,
        ]);

        $product = Product::firstOrFail();
        $response->assertRedirect(route('products.show', $product));

        $this->assertSame(20, $product->stock_qty);
        $this->assertNotNull($product->barcode);
        $this->assertSame(1, $product->stockMovements()->count());

        $this->actingAs($user)->get(route('products.show', $product))->assertOk();
        $this->actingAs($user)->get(route('products.index'))->assertOk();
        $this->actingAs($user)->get(route('products.create'))->assertOk();
        $this->actingAs($user)->get(route('products.edit', $product))->assertOk();
        $this->actingAs($user)->get(route('products.barcode', $product))->assertOk();
        $this->actingAs($user)->get(route('categories.index'))->assertOk();
        $this->actingAs($user)->get(route('categories.edit', $category))->assertOk();
    }

    public function test_stock_adjustment_updates_quantity_and_logs_movement(): void
    {
        $user = User::factory()->create();
        $product = Product::create([
            'name' => 'Bottled Water',
            'sku' => 'SKU-002',
            'price' => 0.75,
            'cost' => 0.30,
            'stock_qty' => 10,
        ]);

        $this->actingAs($user)->post(route('products.stock-movements.store', $product), [
            'type' => 'damage',
            'quantity' => 3,
            'note' => 'Broken in transit',
        ])->assertRedirect();

        $this->assertSame(7, $product->fresh()->stock_qty);
        $this->assertSame(-3, $product->stockMovements()->first()->quantity);
    }

    public function test_stock_adjustment_cannot_drop_quantity_below_zero(): void
    {
        $user = User::factory()->create();
        $product = Product::create([
            'name' => 'Chips',
            'sku' => 'SKU-003',
            'price' => 2.00,
            'cost' => 1.20,
            'stock_qty' => 2,
        ]);

        $this->actingAs($user)->post(route('products.stock-movements.store', $product), [
            'type' => 'damage',
            'quantity' => 5,
            'note' => 'Broken in transit',
        ])->assertSessionHasErrors('quantity');

        $this->assertSame(2, $product->fresh()->stock_qty);
    }

    public function test_a_reason_is_required_for_adjustment_and_damage_but_not_restock(): void
    {
        $user = User::factory()->create();
        $product = Product::create([
            'name' => 'Crackers', 'sku' => 'SKU-004', 'price' => 1.5, 'cost' => 0.8, 'stock_qty' => 10,
        ]);

        $this->actingAs($user)->post(route('products.stock-movements.store', $product), [
            'type' => 'damage', 'quantity' => 1,
        ])->assertSessionHasErrors('note');

        $this->actingAs($user)->post(route('products.stock-movements.store', $product), [
            'type' => 'adjustment', 'quantity' => 1,
        ])->assertSessionHasErrors('note');

        $this->actingAs($user)->post(route('products.stock-movements.store', $product), [
            'type' => 'restock', 'quantity' => 1,
        ])->assertSessionDoesntHaveErrors('note');
    }

    public function test_a_products_own_reorder_point_overrides_the_global_low_stock_threshold(): void
    {
        $lowGlobalThreshold = Product::create([
            'name' => 'Soap', 'sku' => 'SKU-600', 'price' => 1, 'cost' => 0.5, 'stock_qty' => 8,
        ]);
        $this->assertFalse($lowGlobalThreshold->isLowStock(5));

        $customReorderPoint = Product::create([
            'name' => 'Shampoo', 'sku' => 'SKU-601', 'price' => 1, 'cost' => 0.5, 'stock_qty' => 8,
            'reorder_point' => 10,
        ]);
        $this->assertTrue($customReorderPoint->isLowStock(5));
    }
}
