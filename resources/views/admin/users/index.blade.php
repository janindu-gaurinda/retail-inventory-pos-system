{{--
================================================================================
FILE PATH: resources/views/admin/users/index.blade.php
DESCRIPTION: Module 11 — User Management Directory, Role-Based Access Control
             (RBAC) Matrix & Staff CRUD Modals (Create, Edit, Role Change,
             Password Reset, and Delete Protection).
================================================================================

EXTENDS MASTER LAYOUT:
- Shared Master Layout: resources/views/layouts/admin.blade.php
  (Automatically injects Shared Sidebar, Shared Top Navbar, Shared Footer,
   Global CSS public/assets/css/ism-admin.css, and Global JS public/assets/js/ism-admin.js)

CONNECTED BACKEND FILES:
- Controller: app/Http/Controllers/Admin/UserController.php
  - index()   -> GET    /admin/users         (route: 'admin.users.index')
  - store()   -> POST   /admin/users         (route: 'admin.users.store')
  - update()  -> PUT    /admin/users/{user}  (route: 'admin.users.update')
  - destroy() -> DELETE /admin/users/{user}  (route: 'admin.users.destroy')
- Model: app/Models/User.php
- Routes File: routes/web.php
================================================================================
--}}

@extends('layouts.admin')

{{-- PART 1: Page Metadata & Dynamic Top Navbar Header --}}
@section('title', 'User Management — ISM System')
@section('page_title', 'User Management & Roles')
@section('page_badge', 'Module 11 · Admin Only')
@section('page_subtitle', 'Manage staff accounts, assign system roles (Admin, Store Manager, Stock Manager, Cashier), and reset credentials.')

{{-- PART 2: Top Navbar Action Buttons --}}
@section('topbar_actions')
    <a href="#rbacMatrixSection" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1">
        <i class="bi bi-shield-check"></i>
        <span class="d-none d-sm-inline">Role Permissions Matrix</span>
    </a>
    <button type="button" class="btn btn-primary btn-sm d-flex align-items-center gap-1 fw-semibold"
            data-bs-toggle="modal" data-bs-target="#createUserModal">
        <i class="bi bi-person-plus-fill"></i>
        <span>Add New User</span>
    </button>
@endsection

{{-- PART 3: Main Page Content --}}
@section('content')

    {{-- Flash Alerts & Validation Errors --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm d-flex align-items-center gap-2 mb-4" role="alert">
            <i class="bi bi-check-circle-fill fs-5"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm d-flex align-items-center gap-2 mb-4" role="alert">
            <i class="bi bi-shield-exclamation fs-5"></i>
            <div>{{ session('error') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
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
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- ====================================================================
         PART 4: Role Summary KPI Cards (5 Cards)
         ==================================================================== --}}
    <div class="row g-3 mb-4">
        {{-- Total Users --}}
        <div class="col-6 col-md-4 col-xl">
            <div class="card kpi-card h-100 p-3 border-start border-4 border-primary">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-secondary small fw-semibold text-uppercase" style="font-size: 0.72rem;">Total Staff</div>
                        <div class="fs-3 fw-bold text-dark mt-1">{{ $stats['total_users'] }}</div>
                        <span class="badge bg-primary-subtle text-primary rounded-pill">All Active Accounts</span>
                    </div>
                    <div class="kpi-icon bg-primary bg-opacity-10 text-primary">
                        <i class="bi bi-people-fill"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Admins --}}
        <div class="col-6 col-md-4 col-xl">
            <div class="card kpi-card h-100 p-3 border-start border-4 border-danger">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-secondary small fw-semibold text-uppercase" style="font-size: 0.72rem;">Admins</div>
                        <div class="fs-3 fw-bold text-dark mt-1">{{ $stats['admins_count'] }}</div>
                        <span class="badge bg-danger-subtle text-danger rounded-pill">All 12 Modules</span>
                    </div>
                    <div class="kpi-icon bg-danger bg-opacity-10 text-danger">
                        <i class="bi bi-shield-lock-fill"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Store Managers --}}
        <div class="col-6 col-md-4 col-xl">
            <div class="card kpi-card h-100 p-3 border-start border-4 border-info">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-secondary small fw-semibold text-uppercase" style="font-size: 0.72rem;">Store Managers</div>
                        <div class="fs-3 fw-bold text-dark mt-1">{{ $stats['store_managers_count'] }}</div>
                        <span class="badge bg-info-subtle text-info-emphasis rounded-pill">Store + POS + Reports</span>
                    </div>
                    <div class="kpi-icon bg-info bg-opacity-10 text-info">
                        <i class="bi bi-shop-window"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Stock Managers --}}
        <div class="col-6 col-md-6 col-xl">
            <div class="card kpi-card h-100 p-3 border-start border-4 border-warning">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-secondary small fw-semibold text-uppercase" style="font-size: 0.72rem;">Stock Managers</div>
                        <div class="fs-3 fw-bold text-dark mt-1">{{ $stats['stock_managers_count'] }}</div>
                        <span class="badge bg-warning-subtle text-warning-emphasis rounded-pill">Inventory &amp; POs Only</span>
                    </div>
                    <div class="kpi-icon bg-warning bg-opacity-10 text-warning">
                        <i class="bi bi-boxes"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Cashiers --}}
        <div class="col-12 col-md-6 col-xl">
            <div class="card kpi-card h-100 p-3 border-start border-4 border-success">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-secondary small fw-semibold text-uppercase" style="font-size: 0.72rem;">POS Cashiers</div>
                        <div class="fs-3 fw-bold text-dark mt-1">{{ $stats['cashiers_count'] }}</div>
                        <span class="badge bg-success-subtle text-success rounded-pill">POS + Own Shift Sales</span>
                    </div>
                    <div class="kpi-icon bg-success bg-opacity-10 text-success">
                        <i class="bi bi-cash-coin"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ====================================================================
         PART 5: Search & Role Filter Bar
         ==================================================================== --}}
    <div class="card table-card mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('admin.users.index') }}" class="row g-2 align-items-center">
                <div class="col-12 col-md-5">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-secondary"></i></span>
                        <input type="text" name="search" value="{{ $search }}"
                               class="form-control border-start-0"
                               placeholder="Search staff by name, email address, or role...">
                    </div>
                </div>
                <div class="col-7 col-md-4">
                    <select name="role" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All System Roles ({{ $stats['total_users'] }})</option>
                        <option value="admin" {{ $roleFilter === 'admin' ? 'selected' : '' }}>
                            Admin ({{ $stats['admins_count'] }})
                        </option>
                        <option value="store_manager" {{ $roleFilter === 'store_manager' ? 'selected' : '' }}>
                            Store Manager ({{ $stats['store_managers_count'] }})
                        </option>
                        <option value="stock_manager" {{ $roleFilter === 'stock_manager' ? 'selected' : '' }}>
                            Stock Manager ({{ $stats['stock_managers_count'] }})
                        </option>
                        <option value="cashier" {{ $roleFilter === 'cashier' ? 'selected' : '' }}>
                            Cashier ({{ $stats['cashiers_count'] }})
                        </option>
                    </select>
                </div>
                <div class="col-5 col-md-3 d-flex gap-2 justify-content-end">
                    <button type="submit" class="btn btn-sm btn-dark px-3">
                        <i class="bi bi-funnel me-1"></i> Filter
                    </button>
                    @if ($search !== '' || $roleFilter !== '')
                        <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-outline-secondary">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- ====================================================================
         PART 6: Users Directory Table
         ==================================================================== --}}
    <div class="card table-card mb-4">
        <div class="card-header bg-white py-3 px-4 d-flex flex-wrap align-items-center justify-content-between gap-2 border-bottom">
            <div>
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-people text-primary me-1"></i> Registered System Users ({{ $users->count() }})
                </h6>
                <small class="text-secondary">Click <strong>Edit / Role</strong> to update permissions or reset a user's password.</small>
            </div>
            <span class="badge bg-light text-secondary border">
                Logged in as: <strong>{{ auth()->user()->name }}</strong> ({{ auth()->user()->roleLabel() }})
            </span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">ID</th>
                        <th>Staff Member</th>
                        <th>Assigned Role</th>
                        <th>Module Access Scope</th>
                        <th>System Activity</th>
                        <th>Joined Date</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $staff)
                        @php
                            $isCurrentUser = auth()->id() === $staff->id;
                            $normalizedRole = $staff->role === 'super_admin' ? 'admin' : $staff->role;

                            $roleBadgeClass = match ($normalizedRole) {
                                'admin' => 'bg-danger-subtle text-danger border border-danger-subtle',
                                'store_manager' => 'bg-info-subtle text-info-emphasis border border-info-subtle',
                                'stock_manager' => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle',
                                'cashier' => 'bg-success-subtle text-success border border-success-subtle',
                                default => 'bg-secondary-subtle text-secondary',
                            };

                            $roleIcon = match ($normalizedRole) {
                                'admin' => 'bi-shield-lock-fill',
                                'store_manager' => 'bi-shop',
                                'stock_manager' => 'bi-boxes',
                                'cashier' => 'bi-cash-stack',
                                default => 'bi-person',
                            };

                            $accessSummary = match ($normalizedRole) {
                                'admin' => 'Full Access (All 12 Modules + Users & Settings)',
                                'store_manager' => 'Inventory, POs, Approve Adjustments, POS & Reports',
                                'stock_manager' => 'Products, Barcodes, Create Adjustments, Suppliers & POs',
                                'cashier' => 'POS Checkout, Own Shift Invoices & Product View (No Cost)',
                                default => 'Standard Access',
                            };

                            $userSales = $salesCounts->get($staff->id);
                            $salesCount = $userSales ? (int) $userSales->count : 0;
                            $salesTotal = $userSales ? (float) $userSales->total_sales : 0.0;
                            $adjCount = (int) ($adjustmentCounts[$staff->id] ?? 0);
                            $poCount = (int) ($poCounts[$staff->id] ?? 0);
                        @endphp
                        <tr class="{{ $isCurrentUser ? 'table-primary bg-opacity-10' : '' }}">
                            <td class="ps-4 fw-semibold text-secondary">#{{ $staff->id }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="avatar-circle flex-shrink-0"
                                         style="background: {{ $normalizedRole === 'admin' ? '#dc2626' : ($normalizedRole === 'store_manager' ? '#0284c7' : ($normalizedRole === 'stock_manager' ? '#d97706' : '#16a34a')) }};">
                                        {{ strtoupper(substr($staff->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <div class="fw-semibold text-dark d-flex align-items-center gap-2">
                                            {{ $staff->name }}
                                            @if ($isCurrentUser)
                                                <span class="badge text-bg-primary" style="font-size: 0.65rem;">YOU</span>
                                            @endif
                                        </div>
                                        <div class="text-secondary small">
                                            <i class="bi bi-envelope me-1"></i>{{ $staff->email }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge rounded-pill px-3 py-2 {{ $roleBadgeClass }}">
                                    <i class="bi {{ $roleIcon }} me-1"></i> {{ $staff->roleLabel() }}
                                </span>
                            </td>
                            <td style="max-width: 260px;">
                                <div class="small text-dark">{{ $accessSummary }}</div>
                            </td>
                            <td>
                                <div class="d-flex flex-wrap gap-1">
                                    <span class="badge bg-light text-dark border" title="Completed POS Invoices">
                                        <i class="bi bi-receipt text-success me-1"></i>{{ $salesCount }} Sales
                                        @if ($salesTotal > 0)
                                            (${{ number_format($salesTotal, 0) }})
                                        @endif
                                    </span>
                                    <span class="badge bg-light text-dark border" title="Stock Adjustments Logged">
                                        <i class="bi bi-sliders text-warning me-1"></i>{{ $adjCount }} Adj
                                    </span>
                                    <span class="badge bg-light text-dark border" title="Purchase Orders Created">
                                        <i class="bi bi-truck text-info me-1"></i>{{ $poCount }} POs
                                    </span>
                                </div>
                            </td>
                            <td class="small text-secondary">
                                {{ $staff->created_at ? $staff->created_at->format('d M Y') : 'N/A' }}
                            </td>
                            <td class="text-end pe-4 text-nowrap">
                                <div class="btn-group btn-group-sm" role="group">
                                    {{-- Edit User & Role Modal Trigger --}}
                                    <button type="button"
                                            class="btn btn-outline-primary text-nowrap"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editUserModal"
                                            data-user-id="{{ $staff->id }}"
                                            data-user-name="{{ $staff->name }}"
                                            data-user-email="{{ $staff->email }}"
                                            data-user-role="{{ $normalizedRole }}"
                                            data-is-self="{{ $isCurrentUser ? '1' : '0' }}"
                                            data-update-url="{{ route('admin.users.update', $staff) }}"
                                            title="Edit Profile, Role or Reset Password">
                                        <i class="bi bi-pencil-square me-1"></i> Edit / Role
                                    </button>

                                    {{-- Delete User Button (Disabled for currently logged-in Admin) --}}
                                    @if ($isCurrentUser)
                                        <button type="button" class="btn btn-outline-secondary text-nowrap" disabled
                                                title="You cannot delete your own active account">
                                            <i class="bi bi-lock"></i>
                                        </button>
                                    @else
                                        <button type="button"
                                                class="btn btn-outline-danger text-nowrap"
                                                title="Delete User"
                                                onclick="if(confirm('Are you sure you want to permanently delete user account {{ addslashes($staff->name) }} ({{ $staff->roleLabel() }})?')) document.getElementById('delete-user-form-{{ $staff->id }}').submit();">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    @endif
                                </div>

                                @if (! $isCurrentUser)
                                    <form id="delete-user-form-{{ $staff->id }}" action="{{ route('admin.users.destroy', $staff) }}" method="POST" class="d-none">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-secondary">
                                <i class="bi bi-people fs-1 d-block mb-2 opacity-50"></i>
                                <div class="fw-semibold">No user accounts match your filter criteria.</div>
                                <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-link">Clear Filters</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ====================================================================
         PART 7: Interactive 12-Module Role-Based Access Control (RBAC) Matrix Reference
         ==================================================================== --}}
    <div id="rbacMatrixSection" class="card table-card">
        <div class="card-header bg-white py-3 px-4 d-flex flex-wrap justify-content-between align-items-center border-bottom">
            <div>
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-shield-check text-primary me-1"></i> System-Wide 12-Module Role Permissions Reference Matrix
                </h6>
                <small class="text-secondary">Enforced automatically across Sidebar Navigation, Controllers, and Blade Views.</small>
            </div>
            <span class="badge bg-primary-subtle text-primary">4 Roles × 12 Modules</span>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered align-middle mb-0 small">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Module / Page</th>
                        <th class="text-center"><span class="badge bg-danger">Admin</span></th>
                        <th class="text-center"><span class="badge bg-info text-dark">Store Manager</span></th>
                        <th class="text-center"><span class="badge bg-warning text-dark">Stock Manager</span></th>
                        <th class="text-center"><span class="badge bg-success">Cashier</span></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="ps-3 fw-semibold">1. Dashboard</td>
                        <td class="text-center text-success fw-semibold">Full (All Stats)</td>
                        <td class="text-center text-success fw-semibold">Full (All Stats)</td>
                        <td class="text-center text-warning-emphasis">Inventory Stats Only</td>
                        <td class="text-center text-info-emphasis">Sales Stats Only</td>
                    </tr>
                    <tr>
                        <td class="ps-3 fw-semibold">2. Products List</td>
                        <td class="text-center text-success">View &amp; Edit</td>
                        <td class="text-center text-success">View &amp; Edit</td>
                        <td class="text-center text-success">View &amp; Edit</td>
                        <td class="text-center text-secondary">View Only (No Cost Price)</td>
                    </tr>
                    <tr>
                        <td class="ps-3 fw-semibold">3. Add / Edit Product</td>
                        <td class="text-center text-success"><i class="bi bi-check-circle-fill"></i></td>
                        <td class="text-center text-success"><i class="bi bi-check-circle-fill"></i></td>
                        <td class="text-center text-success"><i class="bi bi-check-circle-fill"></i></td>
                        <td class="text-center text-danger"><i class="bi bi-x-circle-fill"></i></td>
                    </tr>
                    <tr>
                        <td class="ps-3 fw-semibold">4. Barcode / Label Print</td>
                        <td class="text-center text-success"><i class="bi bi-check-circle-fill"></i></td>
                        <td class="text-center text-success"><i class="bi bi-check-circle-fill"></i></td>
                        <td class="text-center text-success"><i class="bi bi-check-circle-fill"></i></td>
                        <td class="text-center text-danger"><i class="bi bi-x-circle-fill"></i></td>
                    </tr>
                    <tr>
                        <td class="ps-3 fw-semibold">5. Stock Adjustments</td>
                        <td class="text-center text-success"><i class="bi bi-check-circle-fill"></i> (Create &amp; Approve)</td>
                        <td class="text-center text-success"><i class="bi bi-check-circle-fill"></i> (Approve)</td>
                        <td class="text-center text-success"><i class="bi bi-check-circle-fill"></i> (Create)</td>
                        <td class="text-center text-danger"><i class="bi bi-x-circle-fill"></i></td>
                    </tr>
                    <tr>
                        <td class="ps-3 fw-semibold">6. Suppliers Directory</td>
                        <td class="text-center text-success"><i class="bi bi-check-circle-fill"></i></td>
                        <td class="text-center text-success"><i class="bi bi-check-circle-fill"></i></td>
                        <td class="text-center text-success"><i class="bi bi-check-circle-fill"></i></td>
                        <td class="text-center text-danger"><i class="bi bi-x-circle-fill"></i></td>
                    </tr>
                    <tr>
                        <td class="ps-3 fw-semibold">7. Purchase Orders (POs)</td>
                        <td class="text-center text-success"><i class="bi bi-check-circle-fill"></i></td>
                        <td class="text-center text-success"><i class="bi bi-check-circle-fill"></i></td>
                        <td class="text-center text-success"><i class="bi bi-check-circle-fill"></i> (Create &amp; Receive)</td>
                        <td class="text-center text-danger"><i class="bi bi-x-circle-fill"></i></td>
                    </tr>
                    <tr>
                        <td class="ps-3 fw-semibold">8. POS Checkout Screen</td>
                        <td class="text-center text-success"><i class="bi bi-check-circle-fill"></i></td>
                        <td class="text-center text-success"><i class="bi bi-check-circle-fill"></i></td>
                        <td class="text-center text-danger"><i class="bi bi-x-circle-fill"></i></td>
                        <td class="text-center text-success"><i class="bi bi-check-circle-fill"></i></td>
                    </tr>
                    <tr>
                        <td class="ps-3 fw-semibold">9. Sales History / Invoices</td>
                        <td class="text-center text-success"><i class="bi bi-check-circle-fill"></i> (All Shifts)</td>
                        <td class="text-center text-success"><i class="bi bi-check-circle-fill"></i> (All Shifts)</td>
                        <td class="text-center text-danger"><i class="bi bi-x-circle-fill"></i></td>
                        <td class="text-center text-success"><i class="bi bi-check-circle-fill"></i> (Own Shift Only)</td>
                    </tr>
                    <tr>
                        <td class="ps-3 fw-semibold">10. Reports &amp; Analytics</td>
                        <td class="text-center text-success"><i class="bi bi-check-circle-fill"></i></td>
                        <td class="text-center text-success"><i class="bi bi-check-circle-fill"></i> (Sales / Stock)</td>
                        <td class="text-center text-danger"><i class="bi bi-x-circle-fill"></i></td>
                        <td class="text-center text-danger"><i class="bi bi-x-circle-fill"></i></td>
                    </tr>
                    <tr class="table-primary bg-opacity-10">
                        <td class="ps-3 fw-bold">11. User Management (Current Page)</td>
                        <td class="text-center text-success fw-bold"><i class="bi bi-check-circle-fill"></i> Admin Only</td>
                        <td class="text-center text-danger"><i class="bi bi-x-circle-fill"></i></td>
                        <td class="text-center text-danger"><i class="bi bi-x-circle-fill"></i></td>
                        <td class="text-center text-danger"><i class="bi bi-x-circle-fill"></i></td>
                    </tr>
                    <tr>
                        <td class="ps-3 fw-semibold">12. System Settings</td>
                        <td class="text-center text-success fw-bold"><i class="bi bi-check-circle-fill"></i> Admin Only</td>
                        <td class="text-center text-danger"><i class="bi bi-x-circle-fill"></i></td>
                        <td class="text-center text-danger"><i class="bi bi-x-circle-fill"></i></td>
                        <td class="text-center text-danger"><i class="bi bi-x-circle-fill"></i></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

@endsection

{{-- ========================================================================
     PART 8: Create & Edit User Modals (Injected via @push('modals'))
     ======================================================================== --}}
@push('modals')
    {{-- 1. Create New User Modal -> POST /admin/users --}}
    <div class="modal fade" id="createUserModal" tabindex="-1" aria-labelledby="createUserModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <form action="{{ route('admin.users.store') }}" method="POST">
                    @csrf
                    <div class="modal-header bg-dark text-white">
                        <h6 class="modal-title fw-bold" id="createUserModalLabel">
                            <i class="bi bi-person-plus-fill text-info me-2"></i>Add New Staff Account
                        </h6>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required maxlength="120"
                                   placeholder="e.g., Kasun Perera" value="{{ old('name') }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Email Address (Login ID) <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" required maxlength="150"
                                   placeholder="e.g., kasun@ism.com" value="{{ old('email') }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Assign System Role <span class="text-danger">*</span></label>
                            <select name="role" class="form-select" required>
                                <option value="cashier" {{ old('role') === 'cashier' ? 'selected' : '' }}>
                                    Cashier — POS Checkout &amp; Own Shift Sales
                                </option>
                                <option value="stock_manager" {{ old('role') === 'stock_manager' ? 'selected' : '' }}>
                                    Stock Manager — Products, Barcodes, Adjustments, Suppliers &amp; POs
                                </option>
                                <option value="store_manager" {{ old('role') === 'store_manager' ? 'selected' : '' }}>
                                    Store Manager — Full Store Operations, Approve Adjustments, POS &amp; Reports
                                </option>
                                <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>
                                    Admin — Full System Control (All 12 Modules)
                                </option>
                            </select>
                        </div>

                        <div class="row g-2">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Password <span class="text-danger">*</span></label>
                                <input type="password" name="password" class="form-control" required minlength="6"
                                       placeholder="Min 6 characters">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Confirm Password <span class="text-danger">*</span></label>
                                <input type="password" name="password_confirmation" class="form-control" required minlength="6"
                                       placeholder="Repeat password">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-sm px-4 fw-semibold">
                            <i class="bi bi-check2-circle me-1"></i> Create User Account
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- 2. Edit User Profile / Role / Reset Password Modal -> PUT /admin/users/{user} --}}
    <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <form id="editUserForm" method="POST" action="">
                    @csrf
                    @method('PUT')
                    <div class="modal-header bg-primary text-white">
                        <h6 class="modal-title fw-bold" id="editUserModalLabel">
                            <i class="bi bi-pencil-square me-2"></i>Edit User &amp; Role Assignment
                        </h6>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div id="selfEditNotice" class="alert alert-info py-2 px-3 small d-none mb-3">
                            <i class="bi bi-info-circle-fill me-1"></i>
                            You are editing your own active Admin account. Your Admin role is locked for safety.
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="editUserName" class="form-control" required maxlength="120">
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Email Address (Login ID) <span class="text-danger">*</span></label>
                            <input type="email" name="email" id="editUserEmail" class="form-control" required maxlength="150">
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Assigned Role <span class="text-danger">*</span></label>
                            <select name="role" id="editUserRole" class="form-select" required>
                                <option value="admin">Admin — Full System Control (All 12 Modules)</option>
                                <option value="store_manager">Store Manager — Store Operations, POS &amp; Reports</option>
                                <option value="stock_manager">Stock Manager — Inventory, Adjustments, Suppliers &amp; POs</option>
                                <option value="cashier">Cashier — POS Checkout &amp; Own Shift Sales</option>
                            </select>
                        </div>

                        <hr class="my-3">
                        <div class="small fw-semibold text-secondary mb-2">
                            <i class="bi bi-key me-1"></i> Optional Password Reset (Leave blank to keep current password)
                        </div>
                        <div class="row g-2">
                            <div class="col-md-6">
                                <label class="form-label small">New Password</label>
                                <input type="password" name="password" class="form-control form-control-sm" minlength="6"
                                       placeholder="Leave blank to keep">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small">Confirm New Password</label>
                                <input type="password" name="password_confirmation" class="form-control form-control-sm" minlength="6"
                                       placeholder="Confirm new password">
                            </div>
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
@endpush

{{-- ========================================================================
     PART 9: Page-Specific JavaScript for Dynamic Edit Modal Population
     ======================================================================== --}}
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const editUserModal = document.getElementById('editUserModal');
        if (!editUserModal) return;

        editUserModal.addEventListener('show.bs.modal', function (event) {
            const triggerBtn = event.relatedTarget;
            if (!triggerBtn) return;

            const updateUrl = triggerBtn.getAttribute('data-update-url');
            const name = triggerBtn.getAttribute('data-user-name');
            const email = triggerBtn.getAttribute('data-user-email');
            const role = triggerBtn.getAttribute('data-user-role');
            const isSelf = triggerBtn.getAttribute('data-is-self') === '1';

            const form = document.getElementById('editUserForm');
            form.setAttribute('action', updateUrl);

            document.getElementById('editUserName').value = name || '';
            document.getElementById('editUserEmail').value = email || '';
            document.getElementById('editUserRole').value = role || 'cashier';

            const selfNotice = document.getElementById('selfEditNotice');
            if (isSelf) {
                selfNotice.classList.remove('d-none');
            } else {
                selfNotice.classList.add('d-none');
            }
        });
    });
</script>
@endpush
