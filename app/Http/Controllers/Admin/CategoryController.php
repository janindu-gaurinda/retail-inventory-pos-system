<?php

/**
 * ============================================================================
 * FILE PATH: app/Http/Controllers/Admin/CategoryController.php
 * DESCRIPTION: Full CRUD Backend Controller for Product Categories.
 *              Used both from the dedicated Product Categories page
 *              (GET /admin/categories) AND from the inline Quick-Add Category
 *              form modal inside the Add / Edit Product page.
 * ============================================================================
 *
 * ROLE ACCESS PERMISSIONS:
 * - Admin (Owner / IT):               ✓ Full Access (Create, Edit, Delete)
 * - Store Manager:                    ✓ Full Access (Create, Edit, Delete)
 * - Stock Manager (Inventory Clerk):  ✓ Full Access (Create, Edit, Delete)
 * - Cashier:                          ❌ No Access (403 Forbidden)
 *
 * CONNECTED FILES:
 * - Routes File: routes/web.php (Routes: 'admin.categories.*')
 * - Category Model: app/Models/Category.php
 * - Product Model: app/Models/Product.php
 * - Categories Management UI: resources/views/admin/categories/index.blade.php
 * - Add / Edit Product UI: resources/views/admin/products/form.blade.php
 * ============================================================================
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CategoryController extends Controller
{
    /**
     * PART 1: Display the Product Categories Directory Page (GET /admin/categories).
     */
    public function index(Request $request): View
    {
        if (! $request->user()->canManageCategories()) {
            abort(403, 'Unauthorized: Only Admin, Store Manager, and Stock Manager can manage Product Categories.');
        }

        $search = trim((string) $request->query('search', ''));
        $statusFilter = trim((string) $request->query('status', ''));

        $categories = Category::query()
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('category_code', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($statusFilter !== '', fn ($q) => $q->where('status', $statusFilter))
            ->orderBy('name')
            ->get();

        // Count products per category name
        $productCounts = Product::query()
            ->selectRaw('category, COUNT(*) as count, COALESCE(SUM(stock_quantity), 0) as total_stock')
            ->groupBy('category')
            ->get()
            ->keyBy('category');

        $allCategories = Category::all();
        $summary = [
            'total_categories' => $allCategories->count(),
            'active_categories' => $allCategories->where('status', 'active')->count(),
            'inactive_categories' => $allCategories->where('status', 'inactive')->count(),
            'total_categorized_products' => Product::count(),
        ];

        return view('admin.categories.index', [
            'categories' => $categories,
            'productCounts' => $productCounts,
            'summary' => $summary,
            'filters' => [
                'search' => $search,
                'status' => $statusFilter,
            ],
        ]);
    }

    /**
     * PART 2: Store a Newly Created Product Category (POST /admin/categories).
     *         Works from both /admin/categories and the inline modal on Add/Edit Product.
     */
    public function store(Request $request): RedirectResponse
    {
        if (! $request->user()->canManageCategories()) {
            abort(403, 'Unauthorized: Only Admin, Store Manager, and Stock Manager can create Product Categories.');
        }

        $validated = $request->validate([
            'category_code' => ['required', 'string', 'max:40', 'unique:categories,category_code'],
            'name' => ['required', 'string', 'max:120', 'unique:categories,name'],
            'description' => ['nullable', 'string', 'max:500'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        $category = Category::create($validated);

        // If created from the Add/Edit Product page modal, redirect back and pre-select the new category
        if ($request->filled('redirect_to')) {
            return redirect($request->input('redirect_to'))
                ->with('success', "Category '{$category->name}' ({$category->category_code}) created and added to the dropdown!")
                ->with('selected_category', $category->name);
        }

        return redirect()
            ->route('admin.categories.index')
            ->with('success', "Category '{$category->name}' ({$category->category_code}) created successfully.");
    }

    /**
     * PART 3: Update an Existing Product Category (PUT /admin/categories/{category}).
     */
    public function update(Request $request, Category $category): RedirectResponse
    {
        if (! $request->user()->canManageCategories()) {
            abort(403, 'Unauthorized: Only Admin, Store Manager, and Stock Manager can edit Product Categories.');
        }

        $validated = $request->validate([
            'category_code' => ['required', 'string', 'max:40', Rule::unique('categories', 'category_code')->ignore($category->id)],
            'name' => ['required', 'string', 'max:120', Rule::unique('categories', 'name')->ignore($category->id)],
            'description' => ['nullable', 'string', 'max:500'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        $oldName = $category->name;
        $category->update($validated);

        // Keep linked products in sync if the category name was renamed
        if ($oldName !== $category->name) {
            Product::where('category', $oldName)->update(['category' => $category->name]);
        }

        return redirect()
            ->route('admin.categories.index')
            ->with('success', "Category '{$category->name}' ({$category->category_code}) updated successfully.");
    }

    /**
     * PART 4: Delete a Product Category (DELETE /admin/categories/{category}).
     */
    public function destroy(Request $request, Category $category): RedirectResponse
    {
        if (! $request->user()->canManageCategories()) {
            abort(403, 'Unauthorized: Only Admin, Store Manager, and Stock Manager can delete Product Categories.');
        }

        $linkedProductsCount = Product::where('category', $category->name)->count();
        if ($linkedProductsCount > 0) {
            return redirect()
                ->route('admin.categories.index')
                ->with('error', "Cannot delete category '{$category->name}' because {$linkedProductsCount} product(s) are currently assigned to it.");
        }

        $name = $category->name;
        $code = $category->category_code;
        $category->delete();

        return redirect()
            ->route('admin.categories.index')
            ->with('success', "Category '{$name}' ({$code}) deleted successfully.");
    }
}
