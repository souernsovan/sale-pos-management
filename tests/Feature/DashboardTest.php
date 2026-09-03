<?php

namespace Tests\Feature;

use App\Livewire\Pos\Terminal;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_shows_sales_totals_top_products_and_low_stock(): void
    {
        $user = User::factory()->create();

        $wellStocked = Product::create([
            'name' => 'Rice 5kg', 'sku' => 'SKU-200', 'barcode' => 'BC-200',
            'price' => 10, 'cost' => 7, 'stock_qty' => 50,
        ]);
        $lowStock = Product::create([
            'name' => 'Cooking Oil', 'sku' => 'SKU-201', 'barcode' => 'BC-201',
            'price' => 5, 'cost' => 3, 'stock_qty' => 2,
        ]);

        Livewire::actingAs($user)->test(Terminal::class)
            ->set('barcode', 'BC-200')->call('scan')
            ->call('checkout');

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Rice 5kg');
        $response->assertSee('Cooking Oil');
        $response->assertSee(number_format(10, 2), false);
    }

    public function test_dashboard_renders_with_no_sales_or_products_yet(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('dashboard'))->assertOk();
    }
}
