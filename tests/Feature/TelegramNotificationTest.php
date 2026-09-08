<?php

namespace Tests\Feature;

use App\Livewire\Pos\Terminal;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class TelegramNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.telegram.bot_token' => 'test-token',
            'services.telegram.chat_id' => '999',
        ]);
    }

    public function test_completing_a_sale_sends_a_telegram_notification(): void
    {
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => true])]);

        $user = User::factory()->create();
        $product = Product::create([
            'name' => 'Cola Can', 'sku' => 'SKU-900', 'barcode' => 'BC-900',
            'price' => 1.25, 'cost' => 0.75, 'stock_qty' => 20, 'reorder_point' => 5,
        ]);

        Livewire::actingAs($user)->test(Terminal::class)
            ->set('barcode', 'BC-900')->call('scan')
            ->call('checkout');

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'api.telegram.org')
                && $request['chat_id'] === '999'
                && str_contains($request['text'], 'New Sale');
        });
    }

    public function test_a_sale_that_drops_stock_to_the_reorder_point_sends_a_low_stock_alert(): void
    {
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => true])]);

        $user = User::factory()->create();
        $product = Product::create([
            'name' => 'Bread', 'sku' => 'SKU-901', 'barcode' => 'BC-901',
            'price' => 2.00, 'cost' => 1.00, 'stock_qty' => 6, 'reorder_point' => 5,
        ]);

        Livewire::actingAs($user)->test(Terminal::class)
            ->set('barcode', 'BC-901')->call('scan')
            ->call('checkout');

        Http::assertSent(fn ($request) => str_contains($request['text'] ?? '', 'Low Stock Alert') && str_contains($request['text'], 'Bread'));
    }

    public function test_selling_a_product_that_is_already_low_stock_does_not_repeat_the_alert(): void
    {
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => true])]);

        $user = User::factory()->create();
        $product = Product::create([
            'name' => 'Milk', 'sku' => 'SKU-902', 'barcode' => 'BC-902',
            'price' => 1.80, 'cost' => 1.10, 'stock_qty' => 3, 'reorder_point' => 5,
        ]);

        Livewire::actingAs($user)->test(Terminal::class)
            ->set('barcode', 'BC-902')->call('scan')
            ->call('checkout');

        Http::assertNotSent(fn ($request) => str_contains($request['text'] ?? '', 'Low Stock Alert'));
    }

    public function test_a_manual_stock_adjustment_crossing_into_low_stock_sends_an_alert(): void
    {
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => true])]);

        $user = User::factory()->create();
        $product = Product::create([
            'name' => 'Chips', 'sku' => 'SKU-903', 'price' => 1.50, 'cost' => 0.90,
            'stock_qty' => 8, 'reorder_point' => 5,
        ]);

        $this->actingAs($user)->post(route('products.stock-movements.store', $product), [
            'type' => 'damage', 'quantity' => 4, 'note' => 'Broken in transit',
        ]);

        Http::assertSent(fn ($request) => str_contains($request['text'] ?? '', 'Low Stock Alert') && str_contains($request['text'], 'Chips'));
    }

    public function test_no_request_is_made_when_telegram_is_not_configured(): void
    {
        config(['services.telegram.bot_token' => null, 'services.telegram.chat_id' => null]);
        Http::fake();

        $user = User::factory()->create();
        $product = Product::create([
            'name' => 'Gum', 'sku' => 'SKU-904', 'barcode' => 'BC-904',
            'price' => 1.00, 'cost' => 0.40, 'stock_qty' => 10,
        ]);

        Livewire::actingAs($user)->test(Terminal::class)
            ->set('barcode', 'BC-904')->call('scan')
            ->call('checkout');

        Http::assertNothingSent();
    }
}
