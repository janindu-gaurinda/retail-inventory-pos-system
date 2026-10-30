{{--
================================================================================
FILE PATH: resources/views/layouts/admin.blade.php
DESCRIPTION: Master Blade Layout for the ISM System.
             Centralizes ALL common HTML <head>, CSS, Sidebar, Top Navbar,
             Flash Alerts, Footer, and JavaScript in ONE place so child pages
             never repeat boilerplate code.
================================================================================

SHARED RESOURCES & PARTIALS LOADED BY THIS LAYOUT:
- Global CSS: public/assets/css/ism-admin.css
- Global JS: public/assets/js/ism-admin.js
- Left Sidebar Partial: resources/views/partials/sidebar.blade.php
- Top Navbar Partial: resources/views/partials/navbar.blade.php
- Bottom Footer Partial: resources/views/partials/footer.blade.php

CHILD VIEWS EXTENDING THIS LAYOUT (@extends('layouts.admin')):
- Admin Dashboard: resources/views/admin/dashboard.blade.php
- Products List: resources/views/admin/products/index.blade.php
- Add / Edit Product: resources/views/admin/products/form.blade.php
================================================================================
--}}
<!DOCTYPE html>
<html lang="en">
<head>
    {{-- ===================================================================
         PART 1: Common Meta Tags, Bootstrap 5 CDN, Icons & Shared CSS
         =================================================================== --}}
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'ISM System')</title>

    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Shared ISM Admin Stylesheet (public/assets/css/ism-admin.css) -->
    <link href="{{ asset('assets/css/ism-admin.css') }}" rel="stylesheet">

    {{-- Optional Page-Specific CSS --}}
    @stack('styles')
</head>
<body>

    {{-- ===================================================================
         PART 2: Shared Left Sidebar Navigation
         File Path: resources/views/partials/sidebar.blade.php
         =================================================================== --}}
    @include('partials.sidebar')

    {{-- ===================================================================
         PART 3: Main Content Wrapper (Shared Topbar + Page Content + Footer)
         =================================================================== --}}
    <div class="main-content">

        {{-- Shared Top Navbar (resources/views/partials/navbar.blade.php) --}}
        @include('partials.navbar')

        {{-- Dynamic Page Body --}}
        <main class="p-3 p-lg-4 flex-grow-1">

            {{-- Shared Flash Success & Validation Error Alerts --}}
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 py-2 px-3 mb-3 rounded-3 shadow-sm" role="alert">
                    <i class="bi bi-check-circle-fill fs-5"></i>
                    <div class="small fw-medium">{{ session('success') }}</div>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show py-2 px-3 mb-3 rounded-3 shadow-sm" role="alert">
                    <div class="fw-semibold small mb-1">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i> Please fix the following errors:
                    </div>
                    <ul class="mb-0 small ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            {{-- Child Page Content Injected Here --}}
            @yield('content')
        </main>

        {{-- Shared Footer (resources/views/partials/footer.blade.php) --}}
        @include('partials.footer')
    </div>

    {{-- Optional Page Modals --}}
    @stack('modals')

    {{-- ===================================================================
         PART 4: Common Bootstrap JS & Shared ISM Admin JS
         File Path: public/assets/js/ism-admin.js
         =================================================================== --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('assets/js/ism-admin.js') }}"></script>

    {{-- Optional Page-Specific Scripts --}}
    @stack('scripts')
</body>
</html>
