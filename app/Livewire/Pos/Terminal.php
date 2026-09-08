<?php

namespace App\Livewire\Pos;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Setting;
use App\Models\StockMovement;
use App\Services\Khqr\KhqrCode;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Terminal extends Component
{
    public string $barcode = '';

    public string $search = '';

    /** @var array<int, array{product_id:int,name:string,sku:string,unit_price:float,quantity:int,stock_qty:int}> */
    public array $cart = [];

    public string $paymentMethod = 'cash';

    public float $discount = 0;

    public ?int $customerId = null;

    public string $customerSearch = '';

    public function mount(): void
    {
        abort_unless(auth()->user()->can('access pos'), 403);
    }

    #[Computed]
    public function customerResults()
    {
        if (trim($this->customerSearch) === '') {
            return collect();
        }

        return Customer::query()
            ->where('name', 'like', "%{$this->customerSearch}%")
            ->orWhere('phone', 'like', "%{$this->customerSearch}%")
            ->orderBy('name')
            ->limit(8)
            ->get();
    }

    #[Computed]
    public function selectedCustomer(): ?Customer
    {
        return $this->customerId ? Customer::find($this->customerId) : null;
    }

    public function selectCustomer(int $customerId): void
    {
        $this->customerId = $customerId;
        $this->customerSearch = '';
    }

    public function clearCustomer(): void
    {
        $this->customerId = null;
    }

    public function scan(): void
    {
        $code = trim($this->barcode);
        $this->barcode = '';
        $this->resetErrorBag('barcode');

        if ($code === '') {
            return;
        }

        $product = Product::where('barcode', $code)->first();

        if (! $product) {
            $this->addError('barcode', "No product found for barcode: {$code}");

            return;
        }

        $this->addToCart($product);
    }

    #[Computed]
    public function searchResults()
    {
        if (trim($this->search) === '') {
            return collect();
        }

        return Product::query()
            ->where('name', 'like', "%{$this->search}%")
            ->orWhere('sku', 'like', "%{$this->search}%")
            ->orderBy('name')
            ->limit(8)
            ->get();
    }

    public function addFromSearch(int $productId): void
    {
        $product = Product::find($productId);

        if ($product) {
            $this->addToCart($product);
        }

        $this->search = '';
    }

    protected function addToCart(Product $product, int $qty = 1): void
    {
        if (isset($this->cart[$product->id])) {
            $this->cart[$product->id]['quantity'] += $qty;
        } else {
            $this->cart[$product->id] = [
                'product_id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'unit_price' => (float) $product->price,
                'quantity' => $qty,
                'stock_qty' => $product->stock_qty,
            ];
        }
    }

    public function incrementQuantity(int $productId): void
    {
        if (isset($this->cart[$productId])) {
            $this->cart[$productId]['quantity']++;
        }
    }

    public function decrementQuantity(int $productId): void
    {
        if (! isset($this->cart[$productId])) {
            return;
        }

        if ($this->cart[$productId]['quantity'] <= 1) {
            $this->removeItem($productId);

            return;
        }

        $this->cart[$productId]['quantity']--;
    }

    public function updateQuantity(int $productId, $quantity): void
    {
        if (! isset($this->cart[$productId])) {
            return;
        }

        $this->cart[$productId]['quantity'] = max(1, (int) $quantity);
    }

    public function removeItem(int $productId): void
    {
        unset($this->cart[$productId]);
    }

    #[Computed]
    public function subtotal(): float
    {
        return collect($this->cart)->sum(fn ($line) => $line['unit_price'] * $line['quantity']);
    }

    #[Computed]
    public function total(): float
    {
        return max(0, $this->subtotal - $this->discount);
    }

    #[Computed]
    public function khqrSvg(): ?string
    {
        if ($this->paymentMethod !== 'bank_transfer' || $this->total <= 0) {
            return null;
        }

        $accountId = Setting::get('bakong_account_id');

        if (! $accountId) {
            return null;
        }

        $payload = KhqrCode::generateIndividual(
            bakongAccountId: $accountId,
            accountName: Setting::get('bakong_account_name', Setting::get('shop_name', config('app.name'))),
            merchantCity: Setting::get('bakong_merchant_city', 'Phnom Penh'),
            amount: $this->total,
        );

        return app('DNS2D')->getBarcodeSVG($payload, 'QRCODE', 4, 4);
    }

    public function checkout()
    {
        abort_unless(auth()->user()->can('access pos'), 403);

        $this->resetErrorBag('cart');

        if (empty($this->cart)) {
            $this->addError('cart', 'Cart is empty.');

            return;
        }

        $this->validate([
            'paymentMethod' => ['required', 'in:cash,bank_transfer'],
            'discount' => ['numeric', 'min:0'],
        ]);

        $sale = DB::transaction(function () {
            $products = Product::whereIn('id', array_column($this->cart, 'product_id'))
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($this->cart as $line) {
                $product = $products[$line['product_id']];

                if ($line['quantity'] > $product->stock_qty) {
                    throw ValidationException::withMessages([
                        'cart' => "Not enough stock for {$product->name} (have {$product->stock_qty}, requested {$line['quantity']}).",
                    ]);
                }
            }

            $subtotal = $this->subtotal;
            $discount = min($this->discount, $subtotal);

            $sale = Sale::create([
                'customer_id' => $this->customerId,
                'user_id' => auth()->id(),
                'subtotal' => $subtotal,
                'discount' => $discount,
                'total' => $subtotal - $discount,
                'payment_method' => $this->paymentMethod,
                'status' => Sale::STATUS_COMPLETED,
            ]);

            foreach ($this->cart as $line) {
                $product = $products[$line['product_id']];

                $sale->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $line['quantity'],
                    'unit_price' => $line['unit_price'],
                    'subtotal' => $line['unit_price'] * $line['quantity'],
                ]);

                $product->decrement('stock_qty', $line['quantity']);
                $product->stockMovements()->create([
                    'sale_id' => $sale->id,
                    'type' => StockMovement::TYPE_SALE,
                    'quantity' => -$line['quantity'],
                    'note' => "Sale #{$sale->id}",
                    'created_by' => auth()->id(),
                ]);
            }

            return $sale;
        });

        $this->cart = [];
        $this->discount = 0;
        $this->customerId = null;

        $this->redirect(route('sales.show', $sale), navigate: false);
    }

    public function render()
    {
        return view('livewire.pos.terminal');
    }
}
