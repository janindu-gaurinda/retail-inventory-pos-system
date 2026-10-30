<?php

/**
 * ============================================================================
 * FILE PATH: app/Http/Controllers/Admin/ProductController.php
 * DESCRIPTION: Full CRUD Backend Controller for ISM Products Modules:
 *              - Module 2: Products List (GET /admin/products)
 *              - Module 3: Add / Edit Product Dedicated Page
 *                          (GET /admin/products/create & GET /admin/products/{product}/edit)
 *              - Store, Update, Delete actions with Role Protection.
 * ============================================================================
 *
 * CONNECTED FILES:
 * - Routes File: routes/web.php (Routes: 'admin.products.*')
 * - Product Model (Database): app/Models/Product.php
 * - User Model (Permissions): app/Models/User.php
 * - Products List UI: resources/views/admin/products/index.blade.php
 * - Add / Edit Product UI: resources/views/admin/products/form.blade.php
 * - Barcode / Label Print UI: resources/views/admin/products/barcodes.blade.php
 * ============================================================================
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductController extends Controller
{
    /**
     * PART 1: Display the Products List UI with Search, Filters & Summary KPIs (READ).
     *
     * Route: GET /admin/products (route name: 'admin.products.index')
     * UI View Path: resources/views/admin/products/index.blade.php
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $category = trim((string) $request->query('category', ''));
        $stockFilter = trim((string) $request->query('stock_status', ''));

        $query = Product::query();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%")
                    ->orWhere('supplier', 'like', "%{$search}%");
            });
        }

        if ($category !== '') {
            $query->where('category', $category);
        }

        if ($stockFilter === 'in_stock') {
            $query->whereColumn('stock_quantity', '>', 'min_stock_level');
        } elseif ($stockFilter === 'low_stock') {
            $query->where('stock_quantity', '>', 0)
                ->whereColumn('stock_quantity', '<=', 'min_stock_level');
        } elseif ($stockFilter === 'out_of_stock') {
            $query->where('stock_quantity', '<=', 0);
        }

        $products = $query->orderByDesc('id')->get();

        $allProducts = Product::all();
        $summary = [
            'total_products' => $allProducts->count(),
            'total_categories' => $allProducts->pluck('category')->unique()->count(),
            'low_stock_count' => $allProducts->filter(fn (Product $p) => $p->stock_quantity > 0 && $p->stock_quantity <= $p->min_stock_level)->count(),
            'out_of_stock_count' => $allProducts->filter(fn (Product $p) => $p->stock_quantity <= 0)->count(),
            'total_cost_value' => $allProducts->sum(fn (Product $p) => (float) $p->cost_price * max(0, $p->stock_quantity)),
            'total_retail_value' => $allProducts->sum(fn (Product $p) => (float) $p->selling_price * max(0, $p->stock_quantity)),
        ];

        $categories = $this->availableCategories();

        return view('admin.products.index', [
            'products' => $products,
            'summary' => $summary,
            'categories' => $categories,
            'suppliers' => $this->availableSuppliers(),
            'filters' => [
                'search' => $search,
                'category' => $category,
                'stock_status' => $stockFilter,
            ],
        ]);
    }

    /**
     * PART 2: Display the Dedicated "Add Product" Form Page UI.
     *
     * Route: GET /admin/products/create (route name: 'admin.products.create')
     * UI View Path: resources/views/admin/products/form.blade.php
     */
    public function create(Request $request): View
    {
        // Role Check: Cashiers cannot access Add / Edit Product page
        if (! $request->user()->canManageProducts()) {
            abort(403, 'Unauthorized: Cashier role cannot access Add / Edit Product.');
        }

        return view('admin.products.form', [
            'product' => null,
            'isEdit' => false,
            'categories' => $this->availableCategories(),
            'suppliers' => $this->availableSuppliers(),
            'recentProducts' => Product::orderByDesc('updated_at')->take(8)->get(),
        ]);
    }

    /**
     * PART 3: Display the Dedicated "Edit Product" Form Page UI.
     *
     * Route: GET /admin/products/{product}/edit (route name: 'admin.products.edit')
     * UI View Path: resources/views/admin/products/form.blade.php
     */
    public function edit(Request $request, Product $product): View
    {
        // Role Check: Cashiers cannot access Add / Edit Product page
        if (! $request->user()->canManageProducts()) {
            abort(403, 'Unauthorized: Cashier role cannot access Add / Edit Product.');
        }

        return view('admin.products.form', [
            'product' => $product,
            'isEdit' => true,
            'categories' => $this->availableCategories(),
            'suppliers' => $this->availableSuppliers(),
            'recentProducts' => Product::orderByDesc('updated_at')->take(8)->get(),
        ]);
    }

    /**
     * PART 3B: Display the Dedicated "Barcode / Label Print" Page UI (Module 4).
     *
     * Route: GET /admin/products/barcodes (route name: 'admin.products.barcodes')
     * UI View Path: resources/views/admin/products/barcodes.blade.php
     */
    public function barcodes(Request $request): View
    {
        // Role Check: Cashiers cannot access Barcode / Label Print
        if (! $request->user()->canManageProducts()) {
            abort(403, 'Unauthorized: Cashier role cannot access Barcode / Label Print.');
        }

        $products = Product::where('status', 'active')->orderBy('name')->get();
        $selectedProductId = (int) $request->query('product_id', $products->first()->id ?? 0);

        return view('admin.products.barcodes', [
            'products' => $products,
            'selectedProductId' => $selectedProductId,
            'categories' => $this->availableCategories(),
        ]);
    }

    /**
     * PART 4: Store a Newly Created Product in the Database (CREATE).
     *
     * Route: POST /admin/products (route name: 'admin.products.store')
     * Form Source: resources/views/admin/products/form.blade.php OR index.blade.php modal
     */
    public function store(Request $request): RedirectResponse
    {
        if (! $request->user()->canManageProducts()) {
            abort(403, 'Unauthorized: Your role has View Only permission on Products.');
        }

        $validated = $request->validate([
            'sku' => ['required', 'string', 'max:50', 'unique:products,sku'],
            'barcode' => ['required', 'string', 'max:50', 'unique:products,barcode'],
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:100'],
            'supplier' => ['nullable', 'string', 'max:150'],
            'unit' => ['required', 'string', 'max:30'],
            'cost_price' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'min_stock_level' => ['required', 'integer', 'min:0'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $product = Product::create($validated);

        // If user clicked "Save & Add Another", stay on the Add Product page
        if ($request->boolean('save_and_new')) {
            return redirect()
                ->route('admin.products.create')
                ->with('success', "Product '{$product->name}' ({$product->sku}) created! Ready to add another.");
        }

        return redirect()
            ->route('admin.products.index')
            ->with('success', "Product '{$product->name}' ({$product->sku}) added successfully.");
    }

    /**
     * PART 5: Update an Existing Product in the Database (UPDATE).
     *
     * Route: PUT /admin/products/{product} (route name: 'admin.products.update')
     * Form Source: resources/views/admin/products/form.blade.php OR index.blade.php modal
     */
    public function update(Request $request, Product $product): RedirectResponse
    {
        if (! $request->user()->canManageProducts()) {
            abort(403, 'Unauthorized: Your role has View Only permission on Products.');
        }

        $validated = $request->validate([
            'sku' => ['required', 'string', 'max:50', Rule::unique('products', 'sku')->ignore($product->id)],
            'barcode' => ['required', 'string', 'max:50', Rule::unique('products', 'barcode')->ignore($product->id)],
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:100'],
            'supplier' => ['nullable', 'string', 'max:150'],
            'unit' => ['required', 'string', 'max:30'],
            'cost_price' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'min_stock_level' => ['required', 'integer', 'min:0'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $product->update($validated);

        return redirect()
            ->route('admin.products.index')
            ->with('success', "Product '{$product->name}' ({$product->sku}) updated successfully.");
    }

    /**
     * PART 6: Delete a Product from the Database (DELETE).
     *
     * Route: DELETE /admin/products/{product} (route name: 'admin.products.destroy')
     */
    public function destroy(Request $request, Product $product): RedirectResponse
    {
        if (! $request->user()->canManageProducts()) {
            abort(403, 'Unauthorized: Your role has View Only permission on Products.');
        }

        $name = $product->name;
        $sku = $product->sku;
        $product->delete();

        return redirect()
            ->route('admin.products.index')
            ->with('success', "Product '{$name}' ({$sku}) deleted from inventory.");
    }

    /**
     * Helper: List of active product categories from the 'categories' database table.
     */
    private function availableCategories()
    {
        return Category::where('status', 'active')
            ->pluck('name')
            ->merge(Product::pluck('category'))
            ->filter()
            ->unique()
            ->sort()
            ->values();
    }

    /**
     * Helper: List of active product suppliers from the 'suppliers' database table.
     */
    private function availableSuppliers()
    {
        return Supplier::where('status', 'active')
            ->pluck('company_name')
            ->merge(Product::pluck('supplier'))
            ->filter()
            ->unique()
            ->sort()
            ->values();
    }
}
