<?php

namespace Tests\Feature;

use App\Livewire\Pos\Terminal;
use App\Models\Customer;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CustomerManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_create_and_view_a_customer(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('customers.store'), [
            'name' => 'Jane Doe',
            'phone' => '555-1234',
        ])->assertRedirect();

        $customer = Customer::firstOrFail();

        $this->actingAs($user)->get(route('customers.index'))->assertOk()->assertSee('Jane Doe');
        $this->actingAs($user)->get(route('customers.show', $customer))->assertOk();
        $this->actingAs($user)->get(route('customers.edit', $customer))->assertOk();
    }

    public function test_a_sale_can_be_linked_to_a_customer_from_the_pos_and_shows_in_their_history(): void
    {
        $user = User::factory()->create();
        $customer = Customer::create(['name' => 'Repeat Buyer', 'phone' => '555-9999']);
        $product = Product::create([
            'name' => 'Soap', 'sku' => 'SKU-300', 'barcode' => 'BC-300',
            'price' => 1.00, 'cost' => 0.50, 'stock_qty' => 20,
        ]);

        Livewire::actingAs($user)->test(Terminal::class)
            ->set('barcode', 'BC-300')->call('scan')
            ->call('selectCustomer', $customer->id)
            ->call('checkout');

        $sale = $customer->sales()->firstOrFail();
        $this->assertSame(1, $customer->sales()->count());

        $this->actingAs($user)->get(route('customers.show', $customer))
            ->assertOk()
            ->assertSee('#'.$sale->id);
    }
}
