<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoDataSeeder extends Seeder
{
    /**
     * Populate every business table with realistic sample data for local
     * development/demo purposes. Safe to re-run: categories, suppliers,
     * products and customers are upserted by their natural key, while
     * purchases/sales/manual stock movements are only generated once
     * (guarded by a count check) so re-running never double-stocks or
     * double-sells.
     */
    public function run(): void
    {
        $admin = User::where('email', 'admin@example.com')->first() ?? User::first();

        if (! $admin) {
            $this->command?->warn('DemoDataSeeder skipped: no user exists to attribute sales/purchases to.');

            return;
        }

        $categories = $this->seedCategories();
        $suppliers = $this->seedSuppliers();
        $products = $this->seedProducts($categories, $suppliers);
        $customers = $this->seedCustomers();

        if (Purchase::count() === 0) {
            $this->seedPurchases($products, $suppliers, $admin);
        }

        if (Sale::count() === 0) {
            $this->seedSales($this->reload($products), $customers, $admin);
        }

        if (StockMovement::where('type', StockMovement::TYPE_DAMAGE)->doesntExist()) {
            $this->seedManualAdjustments($this->reload($products), $admin);
        }
    }

    /**
     * Re-fetch products by id so callers see stock_qty as it stands after
     * a prior step (e.g. after purchases have incremented it).
     */
    private function reload(\Illuminate\Support\Collection $products): \Illuminate\Support\Collection
    {
        return Product::whereIn('id', $products->pluck('id'))->get();
    }

    private function seedCategories(): \Illuminate\Support\Collection
    {
        return collect(['Beverages', 'Snacks', 'Dairy', 'Bakery', 'Household', 'Personal Care'])
            ->mapWithKeys(fn ($name) => [$name => Category::firstOrCreate(['name' => $name])]);
    }

    private function seedSuppliers(): \Illuminate\Support\Collection
    {
        $suppliers = [
            ['name' => 'Acme Distributors', 'phone' => '012-555-0100', 'address' => '12 Market St, Phnom Penh', 'notes' => 'Primary beverage & snack supplier. Delivers weekly.'],
            ['name' => 'Global Foods Co', 'phone' => '012-555-0142', 'address' => '88 Riverside Rd, Phnom Penh', 'notes' => 'Dairy and bakery goods.'],
            ['name' => 'Fresh Valley Farms', 'phone' => '012-555-0177', 'address' => '4 Farm Ave, Kandal', 'notes' => null],
            ['name' => 'CleanHome Supplies', 'phone' => '012-555-0199', 'address' => '21 Industrial Rd, Phnom Penh', 'notes' => 'Household & personal care items.'],
        ];

        return collect($suppliers)->mapWithKeys(
            fn ($attrs) => [$attrs['name'] => Supplier::firstOrCreate(['name' => $attrs['name']], $attrs)]
        );
    }

    private function seedProducts(\Illuminate\Support\Collection $categories, \Illuminate\Support\Collection $suppliers): \Illuminate\Support\Collection
    {
        // [name, category, supplier, sku, price, cost, reorder_point]
        $defs = [
            ['Bottled Water 500ml', 'Beverages', 'Acme Distributors', 'SKU-1001', 0.75, 0.35, 30],
            ['Cola Can 330ml', 'Beverages', 'Acme Distributors', 'SKU-1002', 1.00, 0.55, 30],
            ['Orange Juice 1L', 'Beverages', 'Global Foods Co', 'SKU-1003', 2.50, 1.60, 15],
            ['Instant Coffee 100g', 'Beverages', 'Acme Distributors', 'SKU-1004', 4.20, 2.80, 10],
            ['Potato Chips 150g', 'Snacks', 'Acme Distributors', 'SKU-1005', 1.80, 1.10, 20],
            ['Chocolate Bar', 'Snacks', 'Acme Distributors', 'SKU-1006', 1.20, 0.70, 25],
            ['Mixed Nuts 200g', 'Snacks', 'Global Foods Co', 'SKU-1007', 3.50, 2.30, 12],
            ['Crackers 100g', 'Snacks', 'Acme Distributors', 'SKU-1008', 1.50, 0.90, 20],
            ['Fresh Milk 1L', 'Dairy', 'Fresh Valley Farms', 'SKU-1009', 2.20, 1.40, 15],
            ['Cheddar Cheese 200g', 'Dairy', 'Fresh Valley Farms', 'SKU-1010', 3.80, 2.60, 8],
            ['Butter 250g', 'Dairy', 'Fresh Valley Farms', 'SKU-1011', 3.00, 2.00, 10],
            ['Yogurt Cup', 'Dairy', 'Fresh Valley Farms', 'SKU-1012', 1.10, 0.60, 20],
            ['White Bread Loaf', 'Bakery', 'Global Foods Co', 'SKU-1013', 1.60, 0.90, 15],
            ['Croissant', 'Bakery', 'Global Foods Co', 'SKU-1014', 1.40, 0.75, 15],
            ['Dish Soap 500ml', 'Household', 'CleanHome Supplies', 'SKU-1015', 2.80, 1.70, 12],
            ['Laundry Detergent 1kg', 'Household', 'CleanHome Supplies', 'SKU-1016', 5.50, 3.60, 8],
            ['Toilet Paper 4-pack', 'Household', 'CleanHome Supplies', 'SKU-1017', 3.20, 2.00, 15],
            ['Toothpaste', 'Personal Care', 'CleanHome Supplies', 'SKU-1018', 2.40, 1.50, 15],
            ['Shampoo 400ml', 'Personal Care', 'CleanHome Supplies', 'SKU-1019', 4.00, 2.60, 10],
            ['Bar Soap', 'Personal Care', 'CleanHome Supplies', 'SKU-1020', 0.90, 0.45, 25],
        ];

        return collect($defs)->map(function ($def) use ($categories, $suppliers) {
            [$name, $category, $supplier, $sku, $price, $cost, $reorder] = $def;

            $product = Product::firstOrCreate(
                ['sku' => $sku],
                [
                    'category_id' => $categories[$category]->id,
                    'supplier_id' => $suppliers[$supplier]->id,
                    'name' => $name,
                    'price' => $price,
                    'cost' => $cost,
                    'stock_qty' => 0,
                    'reorder_point' => $reorder,
                ]
            );

            if (! $product->barcode) {
                $product->update(['barcode' => sprintf('PRD%08d', $product->id)]);
            }

            return $product;
        });
    }

    private function seedCustomers(): \Illuminate\Support\Collection
    {
        $customers = [
            ['name' => 'Sokha Chan', 'phone' => '012-345-678', 'address' => 'St. 214, Phnom Penh'],
            ['name' => 'Dara Pich', 'phone' => '012-345-679', 'address' => 'St. 63, Phnom Penh'],
            ['name' => 'Mealea Sok', 'phone' => '012-345-680', 'address' => 'St. 271, Phnom Penh'],
            ['name' => 'Vichet Ly', 'phone' => '012-345-681', 'address' => null],
            ['name' => 'Srey Neang', 'phone' => '012-345-682', 'address' => null],
        ];

        return collect($customers)->map(fn ($attrs) => Customer::firstOrCreate(['name' => $attrs['name']], $attrs));
    }

    private function seedPurchases(\Illuminate\Support\Collection $products, \Illuminate\Support\Collection $suppliers, User $admin): void
    {
        foreach ($suppliers as $supplier) {
            $items = $products->where('supplier_id', $supplier->id);

            if ($items->isEmpty()) {
                continue;
            }

            $purchase = Purchase::create([
                'supplier_id' => $supplier->id,
                'user_id' => $admin->id,
                'purchased_at' => now()->subDays(random_int(20, 45)),
                'notes' => 'Initial stock-in.',
                'total' => 0,
            ]);

            $total = 0;

            foreach ($items as $product) {
                $quantity = random_int(40, 90);
                $costPrice = (float) $product->cost;
                $subtotal = round($quantity * $costPrice, 2);
                $total += $subtotal;

                $purchase->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'cost_price' => $costPrice,
                    'subtotal' => $subtotal,
                ]);

                $product->increment('stock_qty', $quantity);
                $product->stockMovements()->create([
                    'purchase_id' => $purchase->id,
                    'type' => StockMovement::TYPE_PURCHASE,
                    'quantity' => $quantity,
                    'note' => "Purchase #{$purchase->id}",
                    'created_by' => $admin->id,
                ]);
            }

            $purchase->update(['total' => $total]);
        }
    }

    private function seedSales(\Illuminate\Support\Collection $products, \Illuminate\Support\Collection $customers, User $admin): void
    {
        $paymentMethods = ['cash', 'bank_transfer'];

        for ($i = 0; $i < 30; $i++) {
            $lineCount = random_int(1, 4);
            $chosen = $products->random(min($lineCount, $products->count()));
            $occurredAt = now()->subDays(random_int(0, 30))->subMinutes(random_int(0, 1440));

            $sale = DB::transaction(function () use ($chosen, $customers, $admin, $paymentMethods) {
                $lines = $chosen->map(function ($product) {
                    $available = min($product->stock_qty, 5);
                    $quantity = $available > 0 ? random_int(1, $available) : 0;

                    return ['product' => $product, 'quantity' => $quantity];
                })->filter(fn ($line) => $line['quantity'] > 0);

                if ($lines->isEmpty()) {
                    return null;
                }

                $subtotal = $lines->sum(fn ($line) => $line['product']->price * $line['quantity']);
                $discount = random_int(0, 10) === 0 ? round($subtotal * 0.1, 2) : 0;

                $sale = Sale::create([
                    'customer_id' => random_int(0, 2) === 0 ? null : $customers->random()->id,
                    'user_id' => $admin->id,
                    'subtotal' => $subtotal,
                    'discount' => $discount,
                    'total' => $subtotal - $discount,
                    'payment_method' => $paymentMethods[array_rand($paymentMethods)],
                    'status' => Sale::STATUS_COMPLETED,
                ]);

                foreach ($lines as $line) {
                    $product = $line['product'];
                    $quantity = $line['quantity'];

                    $sale->items()->create([
                        'product_id' => $product->id,
                        'quantity' => $quantity,
                        'unit_price' => $product->price,
                        'subtotal' => $product->price * $quantity,
                    ]);

                    $product->decrement('stock_qty', $quantity);
                    $product->stockMovements()->create([
                        'sale_id' => $sale->id,
                        'type' => StockMovement::TYPE_SALE,
                        'quantity' => -$quantity,
                        'note' => "Sale #{$sale->id}",
                        'created_by' => $admin->id,
                    ]);
                }

                return $sale;
            });

            if ($sale) {
                DB::table('sales')->where('id', $sale->id)->update([
                    'created_at' => $occurredAt,
                    'updated_at' => $occurredAt,
                ]);
                DB::table('sale_items')->where('sale_id', $sale->id)->update([
                    'created_at' => $occurredAt,
                    'updated_at' => $occurredAt,
                ]);
                DB::table('stock_movements')->where('sale_id', $sale->id)->update([
                    'created_at' => $occurredAt,
                    'updated_at' => $occurredAt,
                ]);
            }
        }
    }

    private function seedManualAdjustments(\Illuminate\Support\Collection $products, User $admin): void
    {
        $damaged = $products->firstWhere('stock_qty', '>', 3);

        if (! $damaged) {
            return;
        }

        $qty = 2;
        $damaged->decrement('stock_qty', $qty);
        $damaged->stockMovements()->create([
            'type' => StockMovement::TYPE_DAMAGE,
            'quantity' => -$qty,
            'note' => 'Damaged during shelving',
            'created_by' => $admin->id,
        ]);

        $adjusted = $products->firstWhere('id', '!=', $damaged->id);

        if ($adjusted) {
            $adjusted->stockMovements()->create([
                'type' => StockMovement::TYPE_ADJUSTMENT,
                'quantity' => 1,
                'note' => 'Manual count correction',
                'created_by' => $admin->id,
            ]);
            $adjusted->increment('stock_qty', 1);
        }
    }
}
