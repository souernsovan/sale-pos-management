<?php

namespace Tests\Feature;

use App\Livewire\Pos\Terminal;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PosCheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_scanning_the_same_barcode_twice_increments_quantity_instead_of_duplicating(): void
    {
        $user = User::factory()->create();
        $product = Product::create([
            'name' => 'Cola Can', 'sku' => 'SKU-100', 'barcode' => 'BC-100',
            'price' => 1.25, 'cost' => 0.75, 'stock_qty' => 10,
        ]);

        Livewire::actingAs($user)->test(Terminal::class)
            ->set('barcode', 'BC-100')->call('scan')
            ->set('barcode', 'BC-100')->call('scan')
            ->assertSet("cart.{$product->id}.quantity", 2)
            ->assertCount('cart', 1);
    }

    public function test_unknown_barcode_shows_an_error_and_adds_nothing(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)->test(Terminal::class)
            ->set('barcode', 'DOES-NOT-EXIST')->call('scan')
            ->assertHasErrors('barcode')
            ->assertCount('cart', 0);
    }

    public function test_completing_a_sale_creates_records_and_decrements_stock(): void
    {
        $user = User::factory()->create();
        $product = Product::create([
            'name' => 'Bread', 'sku' => 'SKU-101', 'barcode' => 'BC-101',
            'price' => 2.00, 'cost' => 1.00, 'stock_qty' => 5,
        ]);

        Livewire::actingAs($user)->test(Terminal::class)
            ->set('barcode', 'BC-101')->call('scan')
            ->call('incrementQuantity', $product->id)
            ->set('paymentMethod', 'cash')
            ->call('checkout')
            ->assertRedirect();

        $this->assertSame(3, $product->fresh()->stock_qty);

        $sale = Sale::firstOrFail();
        $this->assertSame(1, $sale->items()->count());
        $this->assertSame('4.00', (string) $sale->total);
        $this->assertSame(-2, $product->stockMovements()->first()->quantity);
    }

    public function test_checkout_is_blocked_when_cart_quantity_exceeds_current_stock(): void
    {
        $user = User::factory()->create();
        $product = Product::create([
            'name' => 'Eggs', 'sku' => 'SKU-102', 'barcode' => 'BC-102',
            'price' => 3.00, 'cost' => 2.00, 'stock_qty' => 1,
        ]);

        Livewire::actingAs($user)->test(Terminal::class)
            ->set('barcode', 'BC-102')->call('scan')
            ->call('incrementQuantity', $product->id) // now qty 2, stock only 1
            ->call('checkout')
            ->assertHasErrors('cart');

        $this->assertSame(1, $product->fresh()->stock_qty);
        $this->assertSame(0, Sale::count());
    }

    public function test_pos_sales_and_receipt_pages_render(): void
    {
        $user = User::factory()->create();
        $product = Product::create([
            'name' => 'Juice', 'sku' => 'SKU-104', 'barcode' => 'BC-104',
            'price' => 2.50, 'cost' => 1.50, 'stock_qty' => 10,
        ]);

        Livewire::actingAs($user)->test(Terminal::class)
            ->set('barcode', 'BC-104')->call('scan')
            ->call('checkout');

        $sale = Sale::firstOrFail();

        $this->actingAs($user)->get(route('pos.index'))->assertOk();
        $this->actingAs($user)->get(route('sales.index'))->assertOk();
        $this->actingAs($user)->get(route('sales.show', $sale))->assertOk();
        $this->actingAs($user)->get(route('sales.receipt', $sale))->assertOk();
        $this->actingAs($user)->get(route('sales.receipt.pdf', $sale))->assertOk();
    }

    public function test_voiding_a_sale_restores_stock(): void
    {
        $user = User::factory()->create();
        $product = Product::create([
            'name' => 'Milk', 'sku' => 'SKU-103', 'barcode' => 'BC-103',
            'price' => 1.80, 'cost' => 1.10, 'stock_qty' => 10,
        ]);

        Livewire::actingAs($user)->test(Terminal::class)
            ->set('barcode', 'BC-103')->call('scan')
            ->call('checkout');

        $sale = Sale::firstOrFail();
        $this->assertSame(9, $product->fresh()->stock_qty);

        $this->actingAs($user)->post(route('sales.void', $sale))->assertRedirect();

        $this->assertSame(10, $product->fresh()->stock_qty);
        $this->assertSame('voided', $sale->fresh()->status);
    }

    public function test_khqr_code_appears_for_bank_transfer_once_a_bakong_account_is_configured(): void
    {
        $user = User::factory()->create();
        $product = Product::create([
            'name' => 'Water', 'sku' => 'SKU-105', 'barcode' => 'BC-105',
            'price' => 1.00, 'cost' => 0.50, 'stock_qty' => 10,
        ]);

        Livewire::actingAs($user)->test(Terminal::class)
            ->set('barcode', 'BC-105')->call('scan')
            ->set('paymentMethod', 'bank_transfer')
            ->assertSet('khqrSvg', null);

        Setting::set('bakong_account_id', 'shop@bank');
        Setting::set('bakong_account_name', 'Demo Shop');
        Setting::set('bakong_merchant_city', 'Phnom Penh');

        Livewire::actingAs($user)->test(Terminal::class)
            ->set('barcode', 'BC-105')->call('scan')
            ->set('paymentMethod', 'bank_transfer')
            ->assertSee('<svg', false);
    }
}
