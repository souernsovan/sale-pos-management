<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_update_shop_settings(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->put(route('settings.update'), [
            'shop_name' => 'My Home Shop',
            'shop_address' => '123 Main St',
            'currency_symbol' => '$',
            'tax_rate' => 7.5,
            'low_stock_threshold' => 10,
        ])->assertRedirect(route('settings.edit'));

        $this->assertSame('My Home Shop', Setting::get('shop_name'));
        $this->assertSame('10', Setting::get('low_stock_threshold'));

        $this->actingAs($user)->get(route('settings.edit'))->assertOk()->assertSee('My Home Shop');
    }

    public function test_owner_can_save_bakong_khqr_settings(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->put(route('settings.update'), [
            'shop_name' => 'My Home Shop',
            'currency_symbol' => '$',
            'tax_rate' => 0,
            'low_stock_threshold' => 5,
            'bakong_account_id' => 'shop@bank',
            'bakong_account_name' => 'My Home Shop',
            'bakong_merchant_city' => 'Phnom Penh',
        ])->assertRedirect(route('settings.edit'));

        $this->assertSame('shop@bank', Setting::get('bakong_account_id'));
        $this->assertSame('Phnom Penh', Setting::get('bakong_merchant_city'));
    }

}
