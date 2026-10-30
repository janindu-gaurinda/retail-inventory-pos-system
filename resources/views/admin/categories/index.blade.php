{{--
================================================================================
FILE PATH: resources/views/admin/categories/index.blade.php
DESCRIPTION: Product Categories Directory Page UI with Full CRUD (Create, Edit,
             Delete) for populating the Category dropdown in Add / Edit Product.
================================================================================

EXTENDS MASTER LAYOUT:
- Shared Master Layout: resources/views/layouts/admin.blade.php

ROLE ACCESS PERMISSIONS:
- Admin (Owner / IT):               ✓ Full Access (Create, Edit, Delete)
- Store Manager:                    ✓ Full Access (Create, Edit, Delete)
- Stock Manager (Inventory Clerk):  ✓ Full Access (Create, Edit, Delete)
- Cashier:                          ❌ No Access (403 Forbidden)

CONNECTED BACKEND FILES:
- Controller: app/Http/Controllers/Admin/CategoryController.php
  - index()   -> GET    /admin/categories             (route: 'admin.categories.index')
  - store()   -> POST   /admin/categories             (route: 'admin.categories.store')
  - update()  -> PUT    /admin/categories/{category}  (route: 'admin.categories.update')
  - destroy() -> DELETE /admin/categories/{category}  (route: 'admin.categories.destroy')
- Model: app/Models/Category.php
- Routes File: routes/web.php
================================================================================
--}}

@extends('layouts.admin')

@section('title', 'Product Categories — ISM System')
@section('page_title', 'Product Categories')
@section('page_badge', 'Admin · Store Manager · Stock Manager')
@section('page_subtitle', 'Create, edit, and manage product categories used in the Add / Edit Product dropdown.')

@section('topbar_actions')
<a href="{{ route('admin.products.create') }}" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1">
    <i class="bi bi-plus-square"></i>
    <span class="d-none d-sm-inline">Add Product</span>
</a>
<button type="button" class="btn btn-primary btn-sm d-flex align-items-center gap-1 fw-semibold"
    data-bs-toggle="modal" data-bs-target="#createCategoryModal">
    <i class="bi bi-plus-lg"></i>
    <span>Add New Category</span>
</button>
@endsection

@section('content')

@if (session('success'))
<div class="alert alert-success alert-dismissible fade show border-0 shadow-sm d-flex align-items-center gap-2 mb-4" role="alert">
    <i class="bi bi-check-circle-fill fs-5"></i>
    <div>{{ session('success') }}</div>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if (session('error'))
<div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm d-flex align-items-center gap-2 mb-4" role="alert">
    <i class="bi bi-exclamation-triangle-fill fs-5"></i>
    <div>{{ session('error') }}</div>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if ($errors->any())
<div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
    <div class="fw-semibold mb-1"><i class="bi bi-exclamation-triangle-fill me-1"></i> Please fix the following errors:</div>
    <ul class="mb-0 small">
        @foreach ($errors->all() as $err)
        <li>{{ $err }}</li>
        @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

{{-- PART 1: Summary KPI Cards --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="card kpi-card h-100 p-3 border-start border-4 border-primary">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="text-secondary small fw-semibold text-uppercase" style="font-size: 0.72rem;">Total Categories</div>
                    <div class="fs-3 fw-bold text-dark mt-1">{{ $summary['total_categories'] }}</div>
                    <span class="badge bg-primary-subtle text-primary rounded-pill">Database Records</span>
                </div>
                <div class="kpi-icon bg-primary bg-opacity-10 text-primary">
                    <i class="bi bi-tags-fill"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-xl-3">
        <div class="card kpi-card h-100 p-3 border-start border-4 border-success">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="text-secondary small fw-semibold text-uppercase" style="font-size: 0.72rem;">Active in Dropdown</div>
                    <div class="fs-3 fw-bold text-dark mt-1">{{ $summary['active_categories'] }}</div>
                    <span class="badge bg-success-subtle text-success rounded-pill">Selectable in Products</span>
                </div>
                <div class="kpi-icon bg-success bg-opacity-10 text-success">
                    <i class="bi bi-check2-circle"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-xl-3">
        <div class="card kpi-card h-100 p-3 border-start border-4 border-secondary">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="text-secondary small fw-semibold text-uppercase" style="font-size: 0.72rem;">Inactive Categories</div>
                    <div class="fs-3 fw-bold text-dark mt-1">{{ $summary['inactive_categories'] }}</div>
                    <span class="badge bg-secondary-subtle text-secondary rounded-pill">Hidden from Dropdown</span>
                </div>
                <div class="kpi-icon bg-secondary bg-opacity-10 text-secondary">
                    <i class="bi bi-eye-slash"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-xl-3">
        <div class="card kpi-card h-100 p-3 border-start border-4 border-info">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="text-secondary small fw-semibold text-uppercase" style="font-size: 0.72rem;">Linked Products</div>
                    <div class="fs-3 fw-bold text-dark mt-1">{{ $summary['total_categorized_products'] }}</div>
                    <span class="badge bg-info-subtle text-info-emphasis rounded-pill">Catalog Items</span>
                </div>
                <div class="kpi-icon bg-info bg-opacity-10 text-info">
                    <i class="bi bi-box-seam"></i>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- PART 2: Search & Filter Bar --}}
<div class="card table-card mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('admin.categories.index') }}" class="row g-2 align-items-center">
            <div class="col-12 col-md-6">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light"><i class="bi bi-search text-secondary"></i></span>
                    <input type="text" name="search" value="{{ $filters['search'] ?? '' }}"
                        class="form-control" placeholder="Search by category name, code, or description...">
                </div>
            </div>
            <div class="col-6 col-md-3">
                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    <option value="active" {{ ($filters['status'] ?? '') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ ($filters['status'] ?? '') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div class="col-6 col-md-3 d-flex gap-2 justify-content-end">
                <button type="submit" class="btn btn-sm btn-primary px-3">
                    <i class="bi bi-funnel me-1"></i> Filter
                </button>
                <a href="{{ route('admin.categories.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

{{-- PART 3: Categories Table --}}
<div class="card table-card">
    <div class="card-header bg-white py-3 px-4 d-flex flex-wrap justify-content-between align-items-center border-bottom">
        <div>
            <h6 class="fw-bold mb-0 text-dark">
                <i class="bi bi-tags text-primary me-1"></i> Product Categories Directory ({{ $categories->count() }})
            </h6>
            <small class="text-secondary">Accessible by <strong>Admin</strong>, <strong>Store Manager</strong>, and <strong>Stock Manager</strong>.</small>
        </div>
        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#createCategoryModal">
            <i class="bi bi-plus-circle me-1"></i> New Category
        </button>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 small">
            <thead class="table-light">
                <tr>
                    <th class="ps-4">Code</th>
                    <th>Category Name</th>
                    <th>Description</th>
                    <th class="text-center">Products Count</th>
                    <th class="text-center">Total Stock Units</th>
                    <th>Status</th>
                    <th class="text-end pe-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($categories as $cat)
                @php
                $statsRow = $productCounts->get($cat->name);
                $prodCount = $statsRow ? (int) $statsRow->count : 0;
                $stockSum = $statsRow ? (int) $statsRow->total_stock : 0;
                @endphp
                <tr>
                    <td class="ps-4">
                        <code class="fw-semibold text-primary">{{ $cat->category_code }}</code>
                    </td>
                    <td class="fw-bold text-dark">{{ $cat->name }}</td>
                    <td class="text-secondary">{{ $cat->description ?: '—' }}</td>
                    <td class="text-center">
                        <span class="badge bg-light text-dark border">{{ $prodCount }} Products</span>
                    </td>
                    <td class="text-center fw-semibold">{{ number_format($stockSum) }}</td>
                    <td>
                        @if ($cat->status === 'active')
                        <span class="badge text-bg-success">Active</span>
                        @else
                        <span class="badge text-bg-secondary">Inactive</span>
                        @endif
                    </td>
                    <td class="text-end pe-4 text-nowrap">
                        <div class="btn-group btn-group-sm" role="group">
                            <button type="button"
                                class="btn btn-outline-primary text-nowrap"
                                title="Edit Category"
                                onclick='openEditCategoryModal(@json($cat), "{{ route("admin.categories.update", $cat) }}")'>
                                <i class="bi bi-pencil-square me-1"></i>Edit
                            </button>
                            <button type="button"
                                class="btn btn-outline-danger text-nowrap"
                                title="Delete Category"
                                onclick='openDeleteCategoryModal(@json($cat), "{{ route("admin.categories.destroy", $cat) }}")'>
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-5 text-secondary">
                        <i class="bi bi-tags fs-2 d-block mb-2 opacity-50"></i>
                        <div class="fw-semibold">No categories in database yet.</div>
                        <p class="small mb-2">Click <strong>Add New Category</strong> to create categories for the Product form dropdown.</p>
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createCategoryModal">
                            <i class="bi bi-plus-lg me-1"></i> Create First Category
                        </button>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection

@push('modals')
{{-- Create Category Modal --}}
<div class="modal fade" id="createCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('admin.categories.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h6 class="modal-title fw-bold">
                        <i class="bi bi-tags-fill me-2"></i>Add New Product Category
                    </h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Category Code <span class="text-danger">*</span></label>
                        <div class="input-group input-group-sm">
                            <input type="text" name="category_code" id="new_cat_code" class="form-control font-monospace"
                                value="CAT-{{ rand(100, 999) }}" required maxlength="40">
                            <button type="button" class="btn btn-outline-secondary"
                                onclick="document.getElementById('new_cat_code').value = 'CAT-' + Math.floor(100 + Math.random() * 900)">
                                Auto
                            </button>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Category Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required maxlength="120"
                            placeholder="e.g., POS Hardware, Consumables, Labels & Tags">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select" required>
                            <option value="active" selected>Active (Shown in Product Dropdown)</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>

                    <div class="mb-0">
                        <label class="form-label small fw-semibold">Description (Optional)</label>
                        <textarea name="description" rows="2" class="form-control"
                            placeholder="Short description of products in this category..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4 fw-semibold">
                        <i class="bi bi-check2-circle me-1"></i> Create Category
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit Category Modal --}}
<div class="modal fade" id="editCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form id="editCategoryForm" method="POST" action="">
                @csrf
                @method('PUT')
                <div class="modal-header bg-dark text-white">
                    <h6 class="modal-title fw-bold">
                        <i class="bi bi-pencil-square me-2 text-info"></i>Edit Product Category
                    </h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Category Code <span class="text-danger">*</span></label>
                        <input type="text" name="category_code" id="edit_cat_code" class="form-control font-monospace" required maxlength="40">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Category Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="edit_cat_name" class="form-control" required maxlength="120">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Status <span class="text-danger">*</span></label>
                        <select name="status" id="edit_cat_status" class="form-select" required>
                            <option value="active">Active (Shown in Product Dropdown)</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>

                    <div class="mb-0">
                        <label class="form-label small fw-semibold">Description (Optional)</label>
                        <textarea name="description" id="edit_cat_description" rows="2" class="form-control"></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4 fw-semibold">
                        <i class="bi bi-save me-1"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Delete Category Confirmation Modal --}}
<div class="modal fade" id="deleteCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white">
                <h6 class="modal-title fw-bold">
                    <i class="bi bi-exclamation-triangle me-2"></i>Delete Product Category
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="deleteCategoryForm" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-body p-4">
                    <p class="mb-1">Are you sure you want to delete this category?</p>
                    <div class="p-3 bg-light rounded-3 border mt-2">
                        <div class="fw-bold text-dark" id="delete_cat_name"></div>
                        <small class="text-muted" id="delete_cat_code"></small>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger btn-sm fw-semibold px-3">
                        <i class="bi bi-trash me-1"></i> Yes, Delete
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endpush

@push('scripts')
<script>
    function openEditCategoryModal(category, updateUrl) {
        document.getElementById('editCategoryForm').action = updateUrl;
        document.getElementById('edit_cat_code').value = category.category_code || '';
        document.getElementById('edit_cat_name').value = category.name || '';
        document.getElementById('edit_cat_description').value = category.description || '';
        document.getElementById('edit_cat_status').value = category.status || 'active';

        new bootstrap.Modal(document.getElementById('editCategoryModal')).show();
    }

    function openDeleteCategoryModal(category, deleteUrl) {
        document.getElementById('deleteCategoryForm').action = deleteUrl;
        document.getElementById('delete_cat_name').textContent = category.name;
        document.getElementById('delete_cat_code').textContent = 'Code: ' + category.category_code;

        new bootstrap.Modal(document.getElementById('deleteCategoryModal')).show();
    }
</script>
@endpush