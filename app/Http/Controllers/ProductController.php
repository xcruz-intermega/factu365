<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Models\Product;
use App\Models\ProductComponent;
use App\Models\ProductFamily;
use App\Models\StockMovement;
use App\Services\StockService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::query()
            ->with('family:id,name')
            ->search($request->input('search'))
            ->when($request->input('type'), fn ($q, $type) => $q->where('type', $type))
            ->when($request->input('family'), fn ($q, $familyId) => $q->where('product_family_id', $familyId))
            ->when(
                $request->input('sort'),
                fn ($q) => $q->orderBy($request->input('sort'), $request->input('dir', 'asc')),
                fn ($q) => $q->orderBy('name', 'asc')
            )
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Products/Index', [
            'products' => $products,
            'filters' => $request->only(['search', 'type', 'family', 'sort', 'dir']),
            'families' => ProductFamily::orderBy('sort_order')->orderBy('name')->get(['id', 'name', 'parent_id']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Products/Create', [
            'families' => ProductFamily::orderBy('sort_order')->orderBy('name')->get(['id', 'name', 'parent_id']),
        ]);
    }

    public function store(ProductRequest $request)
    {
        Product::create($request->validated());

        return redirect()->route('products.index')
            ->with('success', __('products.flash_created'));
    }

    public function edit(Product $product)
    {
        $product->load(['components.component:id,name,reference,unit_price']);

        return Inertia::render('Products/Edit', [
            'product' => $product,
            'families' => ProductFamily::orderBy('sort_order')->orderBy('name')->get(['id', 'name', 'parent_id']),
            'allProducts' => Product::where('id', '!=', $product->id)->orderBy('name')->get(['id', 'name', 'reference', 'unit_price']),
        ]);
    }

    public function update(ProductRequest $request, Product $product)
    {
        $product->update($request->validated());

        return redirect()->route('products.index')
            ->with('success', __('products.flash_updated'));
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('products.index')
            ->with('success', __('products.flash_deleted'));
    }

    public function storeComponent(Request $request, Product $product)
    {
        $request->validate([
            'component_product_id' => 'required|exists:products,id|different:' . $product->id,
            'quantity' => 'required|numeric|min:0.0001',
        ]);

        // Prevent duplicates
        if ($product->components()->where('component_product_id', $request->component_product_id)->exists()) {
            return back()->withErrors(['component_product_id' => __('products.error_component_exists')]);
        }

        $product->components()->create([
            'component_product_id' => $request->component_product_id,
            'quantity' => $request->quantity,
        ]);

        return back()->with('success', __('products.flash_component_added'));
    }

    public function destroyComponent(Product $product, ProductComponent $component)
    {
        if ($component->parent_product_id !== $product->id) {
            abort(404);
        }

        $component->delete();

        return back()->with('success', __('products.flash_component_deleted'));
    }

    public function stockMovements(Product $product)
    {
        $movements = StockMovement::where('product_id', $product->id)
            ->with(['document:id,document_type,number', 'user:id,name'])
            ->orderByDesc('created_at')
            ->paginate(25)
            ->withQueryString();

        return Inertia::render('Products/StockMovements', [
            'product' => $product->only(['id', 'name', 'reference', 'stock_quantity', 'minimum_stock', 'track_stock']),
            'movements' => $movements,
        ]);
    }

    public function stockAdjustment(Request $request, Product $product)
    {
        $validated = $request->validate([
            'quantity' => 'required|numeric|not_in:0',
            'notes' => 'nullable|string|max:500',
        ]);

        app(StockService::class)->moveStock(
            product: $product,
            quantity: (float) $validated['quantity'],
            type: 'adjustment',
            notes: $validated['notes'] ?? null,
        );

        return back()->with('success', __('products.flash_adjustment_created'));
    }
}
