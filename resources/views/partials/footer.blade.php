{{--
================================================================================
FILE PATH: resources/views/partials/footer.blade.php
DESCRIPTION: Single Reusable Footer for All ISM System Pages.
================================================================================

INCLUDED BY:
- Master Layout: resources/views/layouts/admin.blade.php
================================================================================
--}}
<footer class="px-4 py-3 text-center text-muted small border-top bg-white">
    &copy; {{ date('Y') }} <strong>{{ $systemSettings['store_name'] ?? 'ISM System' }}</strong> ({{ $systemSettings['store_code'] ?? 'BR-CMB-01' }}) &bull; {{ $systemSettings['store_phone'] ?? '' }} &bull; {{ $systemSettings['store_email'] ?? '' }}
</footer>
