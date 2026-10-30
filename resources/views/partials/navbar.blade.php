{{--
================================================================================
FILE PATH: resources/views/partials/navbar.blade.php
DESCRIPTION: Single Reusable Top Navigation Bar for All ISM System Pages.
             Dynamically displays each page's title, badge, subtitle, and
             custom right-hand action buttons via @yield() sections.
================================================================================

INCLUDED BY:
- Master Layout: resources/views/layouts/admin.blade.php

CONNECTED ROUTES & CONTROLLERS:
- Logout Route (POST /logout): routes/web.php -> LoginController@logout
================================================================================
--}}
@php
    $currentUser = auth()->user();
@endphp

<header class="topbar px-3 px-lg-4 py-2 d-flex align-items-center justify-content-between sticky-top">
    {{-- PART 1: Left Side — Mobile Menu Toggle, Dynamic Page Title, Badge & Subtitle --}}
    <div class="d-flex align-items-center gap-3">
        <button class="btn btn-light border d-lg-none" id="openSidebarBtn" aria-label="Toggle Menu">
            <i class="bi bi-list fs-5"></i>
        </button>
        <div>
            <div class="d-flex align-items-center gap-2">
                <h5 class="mb-0 fw-bold text-dark">@yield('page_title', 'ISM Dashboard')</h5>
                @yield('page_badge')
            </div>
            <small class="text-muted d-none d-sm-inline">
                @yield('page_subtitle', 'Inventory & Sales Management Portal')
            </small>
        </div>
    </div>

    {{-- PART 2: Right Side — Page-Specific Action Buttons + User Profile Dropdown --}}
    <div class="d-flex align-items-center gap-2 gap-sm-3">
        {{-- Optional page-specific buttons injected by child views via @section('topbar_actions') --}}
        @yield('topbar_actions')

        <!-- User Account Dropdown -->
        <div class="dropdown">
            <button class="btn btn-light border rounded-pill d-flex align-items-center gap-2 px-2 py-1" data-bs-toggle="dropdown" aria-expanded="false">
                <div class="avatar-circle" style="width: 30px; height: 30px; font-size: 0.75rem;">
                    {{ strtoupper(substr($currentUser->name ?? 'U', 0, 2)) }}
                </div>
                <span class="small fw-semibold d-none d-md-inline">{{ $currentUser->name ?? 'User' }}</span>
                <i class="bi bi-chevron-down small text-muted me-1"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 mt-2">
                <li class="px-3 py-2 border-bottom">
                    <div class="fw-semibold small">{{ $currentUser->name ?? '' }}</div>
                    <div class="text-muted" style="font-size: 0.75rem;">{{ $currentUser->email ?? '' }}</div>
                    <span class="badge text-bg-primary mt-1">{{ $currentUser?->roleLabel() }}</span>
                </li>
                <li>
                    <a class="dropdown-item small py-2" href="{{ route('admin.dashboard') }}">
                        <i class="bi bi-speedometer2 me-2"></i> Dashboard
                    </a>
                </li>
                <li>
                    <a class="dropdown-item small py-2" href="{{ route('admin.products.index') }}">
                        <i class="bi bi-box-seam me-2"></i> Products List
                    </a>
                </li>
                <li><hr class="dropdown-divider my-1"></li>
                <li>
                    {{-- Logout Form -> POST /logout (app/Http/Controllers/Auth/LoginController.php @ logout) --}}
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="dropdown-item text-danger small py-2">
                            <i class="bi bi-box-arrow-right me-2"></i> Sign Out
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</header>
