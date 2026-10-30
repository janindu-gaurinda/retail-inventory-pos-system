{{--
================================================================================
FILE PATH: resources/views/partials/sidebar.blade.php
DESCRIPTION: Single Reusable Left Sidebar Navigation for All ISM System Pages.
             Changing a menu link or icon here automatically updates ALL pages.
================================================================================

INCLUDED BY:
- Master Layout: resources/views/layouts/admin.blade.php

CONNECTED ROUTES & MODELS:
- Routes File: routes/web.php
- Product Model (Live Badge Count): app/Models/Product.php
- User Model (Role Permissions): app/Models/User.php
================================================================================
--}}
@php
    $currentUser = auth()->user();
    $canManageProducts = $currentUser && $currentUser->canManageProducts();
    $sidebarProductCount = \App\Models\Product::count();
    $sidebarCategoryCount = \App\Models\Category::count();
    $sidebarPendingAdjCount = \App\Models\StockAdjustment::where('status', 'pending')->count();
    $sidebarSupplierCount = \App\Models\Supplier::count();
    $sidebarOpenPoCount = \App\Models\PurchaseOrder::where('status', 'ordered')->count();
@endphp

<!-- Mobile Dark Overlay -->
<div id="sidebarOverlay" class="position-fixed top-0 start-0 w-100 h-100 bg-dark bg-opacity-50 d-none" style="z-index: 1025;"></div>

<!-- Shared Left Sidebar -->
<aside id="sidebar" class="sidebar d-flex flex-column justify-content-between p-3 text-white">
    <div>
        {{-- PART 1: Brand Logo & System Title --}}
        <div class="d-flex align-items-center justify-content-between px-2 py-1 mb-3 border-bottom border-secondary border-opacity-25 pb-3">
            <div class="d-flex align-items-center gap-2">
                <div class="rounded-3 bg-primary text-white d-inline-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                    <i class="bi bi-layers-half fs-5"></i>
                </div>
                <div>
                    <div class="fw-bold leading-tight">ISM System</div>
                    <small class="text-white-50" style="font-size: 0.73rem;">Inventory &amp; Sales POS</small>
                </div>
            </div>
            <button class="btn btn-sm btn-link text-white d-lg-none" id="closeSidebarBtn" aria-label="Close Sidebar">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        {{-- PART 2: Section 1 — Overview --}}
        <div class="sidebar-section-title">Overview</div>
        <ul class="nav flex-column">
            <li class="nav-item">
                {{-- Module 1: Dashboard -> routes/web.php ('admin.dashboard') --}}
                <a href="{{ route('admin.dashboard') }}"
                   class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <span class="nav-link-inner">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </span>
                    <span class="badge rounded-pill text-bg-light text-primary" style="font-size: 0.65rem;">All Stats</span>
                </a>
            </li>
        </ul>

        {{-- PART 3: Section 2 — Inventory & Catalog --}}
        <div class="sidebar-section-title">Inventory &amp; Catalog</div>
        <ul class="nav flex-column">
            <li class="nav-item">
                {{-- Module 2: Products List -> routes/web.php ('admin.products.index') --}}
                <a href="{{ route('admin.products.index') }}"
                   class="nav-link {{ request()->routeIs('admin.products.index') ? 'active' : '' }}">
                    <span class="nav-link-inner">
                        <i class="bi bi-box-seam"></i> Products List
                    </span>
                    <span class="badge bg-secondary bg-opacity-25 text-light" style="font-size: 0.68rem;">
                        {{ $sidebarProductCount }}
                    </span>
                </a>
            </li>

            @if ($canManageProducts)
                <li class="nav-item">
                    {{-- Module 3: Add / Edit Product -> routes/web.php ('admin.products.create' / 'admin.products.edit') --}}
                    <a href="{{ route('admin.products.create') }}"
                       class="nav-link {{ request()->routeIs('admin.products.create', 'admin.products.edit') ? 'active' : '' }}">
                        <span class="nav-link-inner">
                            <i class="bi bi-plus-square"></i> Add / Edit Product
                        </span>
                    </a>
                </li>
                <li class="nav-item">
                    {{-- Product Categories -> routes/web.php ('admin.categories.index') --}}
                    <a href="{{ route('admin.categories.index') }}"
                       class="nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                        <span class="nav-link-inner">
                            <i class="bi bi-tags"></i> Product Categories
                        </span>
                        <span class="badge bg-secondary bg-opacity-25 text-light" style="font-size: 0.68rem;">
                            {{ $sidebarCategoryCount }}
                        </span>
                    </a>
                </li>
                <li class="nav-item">
                    {{-- Module 4: Barcode / Label Print -> routes/web.php ('admin.products.barcodes') --}}
                    <a href="{{ route('admin.products.barcodes') }}"
                       class="nav-link {{ request()->routeIs('admin.products.barcodes') ? 'active' : '' }}">
                        <span class="nav-link-inner">
                            <i class="bi bi-upc-scan"></i> Barcode / Label Print
                        </span>
                    </a>
                </li>
                <li class="nav-item">
                    {{-- Module 5: Stock Adjustments -> routes/web.php ('admin.stock-adjustments.index') --}}
                    <a href="{{ route('admin.stock-adjustments.index') }}"
                       class="nav-link {{ request()->routeIs('admin.stock-adjustments.*') ? 'active' : '' }}">
                        <span class="nav-link-inner">
                            <i class="bi bi-sliders"></i> Stock Adjustments
                        </span>
                        @if ($sidebarPendingAdjCount > 0)
                            <span class="badge text-bg-warning" style="font-size: 0.68rem;">
                                {{ $sidebarPendingAdjCount }}
                            </span>
                        @endif
                    </a>
                </li>
            @endif
        </ul>

        {{-- PART 4: Section 3 — Purchasing & Supply --}}
        <div class="sidebar-section-title">Purchasing &amp; Supply</div>
        <ul class="nav flex-column">
            @if ($canManageProducts)
                <li class="nav-item">
                    {{-- Module 6: Suppliers Directory -> routes/web.php ('admin.suppliers.index') --}}
                    <a href="{{ route('admin.suppliers.index') }}"
                       class="nav-link {{ request()->routeIs('admin.suppliers.*') ? 'active' : '' }}">
                        <span class="nav-link-inner">
                            <i class="bi bi-truck"></i> Suppliers Directory
                        </span>
                        <span class="badge bg-secondary bg-opacity-25 text-light" style="font-size: 0.68rem;">
                            {{ $sidebarSupplierCount }}
                        </span>
                    </a>
                </li>
                <li class="nav-item">
                    {{-- Module 7: Purchase Orders (POs) -> routes/web.php ('admin.purchase-orders.index') --}}
                    <a href="{{ route('admin.purchase-orders.index') }}"
                       class="nav-link {{ request()->routeIs('admin.purchase-orders.*') ? 'active' : '' }}">
                        <span class="nav-link-inner">
                            <i class="bi bi-receipt-cutoff"></i> Purchase Orders (POs)
                        </span>
                        @if ($sidebarOpenPoCount > 0)
                            <span class="badge bg-info bg-opacity-25 text-info" style="font-size: 0.68rem;">
                                {{ $sidebarOpenPoCount }}
                            </span>
                        @endif
                    </a>
                </li>
            @endif
        </ul>

        {{-- PART 5: Section 4 — POS & Sales --}}
        <div class="sidebar-section-title">POS &amp; Sales</div>
        <ul class="nav flex-column">
            @if ($currentUser && $currentUser->canAccessPos())
                <li class="nav-item">
                    {{-- Module 8: POS Checkout Screen -> routes/web.php ('admin.pos.index') --}}
                    <a href="{{ route('admin.pos.index') }}"
                       class="nav-link {{ request()->routeIs('admin.pos.*') ? 'active' : '' }}">
                        <span class="nav-link-inner">
                            <i class="bi bi-cart-check"></i> POS Checkout Screen
                        </span>
                        <span class="badge text-bg-success" style="font-size: 0.65rem;">LIVE</span>
                    </a>
                </li>
                <li class="nav-item">
                    {{-- Module 9: Sales History / Invoices -> routes/web.php ('admin.sales.index') --}}
                    <a href="{{ route('admin.sales.index') }}"
                       class="nav-link {{ request()->routeIs('admin.sales.*') ? 'active' : '' }}">
                        <span class="nav-link-inner">
                            <i class="bi bi-journal-text"></i> Sales History / Invoices
                        </span>
                    </a>
                </li>
            @endif
        </ul>

        {{-- PART 6: Section 5 — Administration --}}
        <div class="sidebar-section-title">Administration</div>
        <ul class="nav flex-column mb-3">
            @if ($currentUser && $currentUser->canAccessReports())
                <li class="nav-item">
                    {{-- Module 10: Reports & Analytics -> routes/web.php ('admin.reports.index') --}}
                    <a href="{{ route('admin.reports.index') }}"
                       class="nav-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                        <span class="nav-link-inner">
                            <i class="bi bi-bar-chart-line"></i> Reports &amp; Analytics
                        </span>
                    </a>
                </li>
            @endif
            @if ($currentUser && $currentUser->canManageUsers())
                <li class="nav-item">
                    {{-- Module 11: User Management -> routes/web.php ('admin.users.index') --}}
                    <a href="{{ route('admin.users.index') }}"
                       class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                        <span class="nav-link-inner">
                            <i class="bi bi-people"></i> User Management
                        </span>
                        <span class="badge bg-primary bg-opacity-25 text-info" style="font-size: 0.65rem;">Admin</span>
                    </a>
                </li>
                <li class="nav-item">
                    {{-- Module 12: System Settings -> routes/web.php ('admin.settings.index') --}}
                    <a href="{{ route('admin.settings.index') }}"
                       class="nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                        <span class="nav-link-inner">
                            <i class="bi bi-gear"></i> System Settings
                        </span>
                        <span class="badge bg-primary bg-opacity-25 text-info" style="font-size: 0.65rem;">Admin</span>
                    </a>
                </li>
            @endif
        </ul>
    </div>

    {{-- PART 7: Sidebar Footer — Logged-in User Profile & Sign Out Form
         Submits POST to Route 'logout' -> app/Http/Controllers/Auth/LoginController.php @ logout() --}}
    <div class="border-top border-secondary border-opacity-25 pt-3 mt-2">
        <div class="d-flex align-items-center justify-content-between px-2 mb-2">
            <div class="d-flex align-items-center gap-2 overflow-hidden">
                <div class="avatar-circle flex-shrink-0">
                    {{ strtoupper(substr($currentUser->name ?? 'U', 0, 2)) }}
                </div>
                <div class="text-truncate">
                    <div class="fw-semibold small text-white text-truncate">{{ $currentUser->name ?? 'User' }}</div>
                    <div class="text-white-50" style="font-size: 0.72rem;">{{ $currentUser?->roleLabel() }}</div>
                </div>
            </div>
        </div>
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-outline-light btn-sm w-100 d-flex align-items-center justify-content-center gap-2">
                <i class="bi bi-box-arrow-right"></i> Sign Out
            </button>
        </form>
    </div>
</aside>
