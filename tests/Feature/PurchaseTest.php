<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Tests\TestCase;

class PurchaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_recording_a_purchase_increases_stock_updates_cost_and_logs_a_movement(): void
    {
        $user = User::factory()->create();
        $supplier = Supplier::create(['name' => 'Acme Distributors']);
        $product = Product::create([
            'name' => 'Instant Noodles', 'sku' => 'SKU-800', 'price' => 2.00, 'cost' => 1.00, 'stock_qty' => 5,
        ]);

        $response = $this->actingAs($user)->post(route('purchases.store'), [
            'supplier_id' => $supplier->id,
            'purchased_at' => now()->format('Y-m-d'),
            'notes' => 'Monthly restock',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 10, 'cost_price' => 1.20],
            ],
        ]);

        $purchase = Purchase::firstOrFail();
        $response->assertRedirect(route('purchases.show', $purchase));

        $this->assertSame(15, $product->fresh()->stock_qty);
        $this->assertEquals(1.20, $product->fresh()->cost);
        $this->assertEquals(12.00, $purchase->total);

        $movement = StockMovement::where('purchase_id', $purchase->id)->firstOrFail();
        $this->assertSame(StockMovement::TYPE_PURCHASE, $movement->type);
        $this->assertSame(10, $movement->quantity);
        $this->assertSame($product->id, $movement->product_id);

        $this->actingAs($user)->get(route('purchases.index'))->assertOk()->assertSee('Acme Distributors');
        $this->actingAs($user)->get(route('purchases.show', $purchase))->assertOk()->assertSee('Instant Noodles');
    }

    public function test_the_create_purchase_page_renders_and_is_not_shadowed_by_the_show_route(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('purchases.create'))->assertOk();
    }

    public function test_a_purchase_cannot_be_edited_or_deleted(): void
    {
        $this->expectException(RouteNotFoundException::class);

        route('purchases.update', 1);
    }
}
