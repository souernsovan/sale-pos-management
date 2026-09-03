<?php

namespace Tests\Feature;

use App\Livewire\Pos\Terminal;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ReportsTest extends TestCase
{
    use RefreshDatabase;

    private function completeASale(User $user, Product $product): void
    {
        Livewire::actingAs($user)->test(Terminal::class)
            ->set('barcode', $product->barcode)->call('scan')
            ->call('checkout');
    }

    public function test_reports_page_shows_sales_profit_and_best_sellers(): void
    {
        $user = User::factory()->create();
        $product = Product::create([
            'name' => 'Notebook', 'sku' => 'SKU-400', 'barcode' => 'BC-400',
            'price' => 4.00, 'cost' => 2.50, 'stock_qty' => 10,
        ]);

        $this->completeASale($user, $product);

        $response = $this->actingAs($user)->get(route('reports.index'));

        $response->assertOk();
        $response->assertSee('Notebook');
        $response->assertSee(number_format(1.50, 2), false); // profit per unit
    }

    public function test_report_exports_render_successfully(): void
    {
        $user = User::factory()->create();
        $product = Product::create([
            'name' => 'Pencil', 'sku' => 'SKU-401', 'barcode' => 'BC-401',
            'price' => 0.50, 'cost' => 0.20, 'stock_qty' => 30,
        ]);

        $this->completeASale($user, $product);

        $this->actingAs($user)->get(route('reports.sales.pdf'))->assertOk();
        $this->actingAs($user)->get(route('reports.profit.pdf'))->assertOk();
        $this->actingAs($user)->get(route('reports.best-sellers.pdf'))->assertOk();

        $this->actingAs($user)->get(route('reports.sales.export'))->assertOk();
        $this->actingAs($user)->get(route('reports.profit.export'))->assertOk();
        $this->actingAs($user)->get(route('reports.best-sellers.export'))->assertOk();
    }

    public function test_date_range_filter_excludes_sales_outside_the_range(): void
    {
        $user = User::factory()->create();
        $product = Product::create([
            'name' => 'Pen', 'sku' => 'SKU-402', 'barcode' => 'BC-402',
            'price' => 1.00, 'cost' => 0.40, 'stock_qty' => 10,
        ]);

        $this->completeASale($user, $product);

        $response = $this->actingAs($user)->get(route('reports.index', [
            'date_from' => now()->addDays(2)->toDateString(),
            'date_to' => now()->addDays(5)->toDateString(),
        ]));

        $response->assertOk();
        $response->assertDontSee('Pen');
    }
}
