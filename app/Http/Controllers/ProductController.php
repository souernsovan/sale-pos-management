<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Support\Audit;
use App\Support\Uploads;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $lowStockThreshold = (int) Setting::get('low_stock_threshold', '5');

        $products = Product::query()
            ->with('category')
            ->when($request->filled('search'), fn ($q) => $q->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                    ->orWhere('sku', 'like', "%{$request->search}%")
                    ->orWhere('barcode', 'like', "%{$request->search}%");
            }))
            ->when($request->filled('category_id'), fn ($q) => $q->where('category_id', $request->category_id))
            ->when($request->boolean('low_stock'), fn ($q) => $q->lowStock($lowStockThreshold))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $categories = Category::orderBy('name')->get();

        return view('products.index', compact('products', 'categories', 'lowStockThreshold'));
    }

    public function create(Request $request)
    {
        abort_unless($request->user()->can('create products'), 403);

        $categories = Category::orderBy('name')->get();
        $suppliers = Supplier::orderBy('name')->get();

        return view('products.create', compact('categories', 'suppliers'));
    }

    public function store(StoreProductRequest $request)
    {
        abort_unless($request->user()->can('create products'), 403);

        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', config('filesystems.uploads_disk', 'public'));
        }

        $initialStock = (int) ($data['stock_qty'] ?? 0);
        unset($data['stock_qty']);

        $product = DB::transaction(function () use ($data, $initialStock) {
            $product = Product::create($data + ['stock_qty' => 0]);

            $product->update(['barcode' => $data['barcode'] ?? sprintf('PRD%08d', $product->id)]);

            if ($initialStock > 0) {
                $product->increment('stock_qty', $initialStock);
                $product->stockMovements()->create([
                    'type' => StockMovement::TYPE_RESTOCK,
                    'quantity' => $initialStock,
                    'note' => 'Initial stock on product creation',
                    'created_by' => auth()->id(),
                ]);
            }

            return $product;
        });

        return redirect()->route('products.show', $product)->with('status', 'Product created.');
    }

    public function show(Request $request, Product $product)
    {
        $product->load(['category', 'supplier']);

        $movements = $product->stockMovements()
            ->with('creator')
            ->when($request->filled('movement_type'), fn ($q) => $q->where('type', $request->movement_type))
            ->when($request->filled('date_from'), fn ($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn ($q) => $q->whereDate('created_at', '<=', $request->date_to))
            ->paginate(15, pageName: 'movements_page')
            ->withQueryString();

        $lowStockThreshold = (int) Setting::get('low_stock_threshold', '5');

        return view('products.show', compact('product', 'lowStockThreshold', 'movements'));
    }

    public function edit(Request $request, Product $product)
    {
        abort_unless($request->user()->can('edit products'), 403);

        $categories = Category::orderBy('name')->get();
        $suppliers = Supplier::orderBy('name')->get();

        return view('products.edit', compact('product', 'categories', 'suppliers'));
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        abort_unless($request->user()->can('edit products'), 403);

        $data = $request->validated();

        if ($request->hasFile('image')) {
            if ($product->image) {
                Uploads::disk()->delete($product->image);
            }
            $data['image'] = $request->file('image')->store('products', config('filesystems.uploads_disk', 'public'));
        }

        $product->update($data);

        return redirect()->route('products.show', $product)->with('status', 'Product updated.');
    }

    public function destroy(Request $request, Product $product)
    {
        abort_unless($request->user()->can('delete products'), 403);

        Audit::log('products', "Deleted product \"{$product->name}\" (SKU {$product->sku})", $product, event: 'deleted');

        if ($product->image) {
            Uploads::disk()->delete($product->image);
        }

        $product->delete();

        return redirect()->route('products.index')->with('status', 'Product deleted.');
    }

    public function barcode(Product $product)
    {
        abort_unless($product->barcode, 404);

        return view('products.barcode', compact('product'));
    }
}
