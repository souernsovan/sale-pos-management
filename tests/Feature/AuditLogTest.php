<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_a_user_writes_an_audit_entry(): void
    {
        $owner = User::factory()->create();

        $this->actingAs($owner)->post(route('users.store'), [
            'name' => 'Staff One',
            'email' => 'staff@example.com',
            'password' => 'password123',
            'role' => 'Cashier',
        ]);

        $activity = Activity::inLog('users')->latest('id')->first();

        $this->assertNotNull($activity);
        $this->assertSame('created', $activity->event);
        $this->assertTrue($activity->causer->is($owner));
        $this->assertStringContainsString('Staff One', $activity->description);
    }

    public function test_deleting_a_category_writes_an_audit_entry(): void
    {
        $owner = User::factory()->create();
        $category = Category::create(['name' => 'Snacks']);

        $this->actingAs($owner)->delete(route('categories.destroy', $category));

        $activity = Activity::inLog('categories')->latest('id')->first();

        $this->assertNotNull($activity);
        $this->assertSame($category->id, $activity->subject_id);
        $this->assertSame('deleted', $activity->event);
        $this->assertStringContainsString('Snacks', $activity->description);
    }

    public function test_updating_settings_records_a_before_after_diff(): void
    {
        $owner = User::factory()->create();

        $this->actingAs($owner)->put(route('settings.update'), [
            'shop_name' => 'New Shop Name',
            'currency_symbol' => '$',
            'tax_rate' => 0,
            'low_stock_threshold' => 5,
        ]);

        $activity = Activity::inLog('settings')->latest('id')->first();

        $this->assertNotNull($activity);
        $this->assertSame('New Shop Name', $activity->properties['changes']['shop_name']['after']);
    }

    public function test_audit_log_page_lists_entries_and_is_blocked_for_cashiers(): void
    {
        $owner = User::factory()->create();
        Category::create(['name' => 'Drinks']);
        $this->actingAs($owner)->delete(route('categories.destroy', Category::first()));

        $this->actingAs($owner)->get(route('audit.index'))->assertOk()->assertSee('Drinks');

        $cashier = User::factory()->create();
        $cashier->syncRoles(['Cashier']);
        $this->actingAs($cashier)->get(route('audit.index'))->assertForbidden();
    }
}
