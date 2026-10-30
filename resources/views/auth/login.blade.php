{{--
================================================================================
FILE PATH: resources/views/auth/login.blade.php
DESCRIPTION: Responsive Bootstrap 5 Login Page UI for ISM System.
================================================================================

CONNECTED BACKEND & ROUTE FILES:
- Route (GET /login): routes/web.php -> LoginController@showLoginForm (name: 'login')
- Route (POST /login): routes/web.php -> LoginController@login (name: 'login.submit')
- Backend Controller: app/Http/Controllers/Auth/LoginController.php
- Database Seeder (Default Super Admin): database/seeders/DatabaseSeeder.php
- Redirects After Login To (UI): resources/views/admin/dashboard.blade.php
================================================================================
--}}
<!DOCTYPE html>
<html lang="en">

<head>
    {{-- ===================================================================
         PART 1: Meta Tags, Bootstrap 5 CDN, Icons & Fonts
         =================================================================== --}}
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | ISM System</title>
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    {{-- ===================================================================
         PART 2: Custom Login Page Styles (Brand Colors, Card, Buttons)
         =================================================================== --}}
    <style>
        :root {
            --ism-primary: #1e40af;
            --ism-primary-hover: #1e3a8a;
            --ism-bg-gradient: linear-gradient(135deg, #0f172a 0%, #1e3a8a 50%, #1d4ed8 100%);
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
            min-height: 100vh;
        }

        .login-wrapper {
            min-height: 100vh;
        }

        /* Left side branding panel background & decorative circles */
        .brand-panel {
            background: var(--ism-bg-gradient);
            position: relative;
            overflow: hidden;
        }

        .brand-panel::before {
            content: '';
            position: absolute;
            width: 450px;
            height: 450px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.05);
            top: -120px;
            right: -120px;
        }

        .brand-panel::after {
            content: '';
            position: absolute;
            width: 320px;
            height: 320px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.04);
            bottom: -80px;
            left: -80px;
        }

        .brand-badge {
            width: 56px;
            height: 56px;
            border-radius: 14px;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(8px);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.6rem;
        }

        /* Right side login card container */
        .login-card {
            max-width: 440px;
            width: 100%;
            border: 1px solid #e2e8f0;
            border-radius: 1.25rem;
            background: #ffffff;
            box-shadow: 0 12px 32px rgba(15, 23, 42, 0.06);
        }

        .form-control {
            padding: 0.75rem 1rem;
            border-radius: 0.65rem;
            border-color: #cbd5e1;
            font-size: 0.95rem;
        }

        .form-control:focus {
            border-color: var(--ism-primary);
            box-shadow: 0 0 0 0.25rem rgba(30, 64, 175, 0.15);
        }

        .input-group-text {
            background-color: #f8fafc;
            border-color: #cbd5e1;
            border-radius: 0.65rem;
            color: #64748b;
        }

        .btn-ism {
            background-color: var(--ism-primary);
            border-color: var(--ism-primary);
            color: #fff;
            padding: 0.75rem 1.25rem;
            border-radius: 0.65rem;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .btn-ism:hover,
        .btn-ism:focus {
            background-color: var(--ism-primary-hover);
            border-color: var(--ism-primary-hover);
            color: #fff;
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(30, 64, 175, 0.25);
        }

        .feature-item {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 0.85rem;
            padding: 1rem 1.25rem;
        }

        /* Quick-fill Role Credential Row */
        .cred-row {
            cursor: pointer;
            transition: all 0.15s ease;
            border: 1px solid #e2e8f0;
            background: #ffffff;
        }

        .cred-row:hover {
            border-color: #93c5fd;
            background-color: #eff6ff;
        }
    </style>
</head>

<body>

    <div class="container-fluid p-0">
        <div class="row g-0 login-wrapper">

            {{-- ===============================================================
             PART 3: Left Branding Panel (Visible on Desktop lg+, Hidden on Mobile)
             =============================================================== --}}
            <div class="col-lg-6 d-none d-lg-flex flex-column justify-content-between p-5 text-white brand-panel">
                <!-- Top Logo & System Name -->
                <div class="position-relative z-1 d-flex align-items-center gap-3">
                    <div class="brand-badge">
                        <i class="bi bi-layers-half"></i>
                    </div>
                    <div>
                        <h4 class="mb-0 fw-bold tracking-tight">ISM System</h4>
                        <small class="text-white-50">Integrated Management Portal</small>
                    </div>
                </div>

                <!-- Middle Feature Highlights -->
                <div class="position-relative z-1 my-auto py-5" style="max-width: 500px;">
                    <span class="badge rounded-pill text-bg-light text-primary fw-semibold px-3 py-2 mb-3">
                        <i class="bi bi-shield-lock-fill me-1"></i> Enterprise Access
                    </span>
                    <h1 class="display-6 fw-bold mb-3">Manage Operations with Speed &amp; Precision</h1>
                    <p class="text-white-50 fs-6 mb-4">
                        Sign in to access your ISM dashboard, monitor real-time workflows, and manage organizational resources from any device.
                    </p>

                    <div class="d-grid gap-3">
                        <div class="feature-item d-flex align-items-center gap-3">
                            <i class="bi bi-speedometer2 fs-4 text-info"></i>
                            <div>
                                <div class="fw-semibold">Real-Time Analytics &amp; Tracking</div>
                                <small class="text-white-50">Instant visibility across all system modules</small>
                            </div>
                        </div>
                        <div class="feature-item d-flex align-items-center gap-3">
                            <i class="bi bi-people-fill fs-4 text-info"></i>
                            <div>
                                <div class="fw-semibold">Role-Based Access Control</div>
                                <small class="text-white-50">Secure permissions for administrators and staff</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bottom Copyright -->
                <div class="position-relative z-1 d-flex justify-content-between align-items-center text-white-50 small">
                    <span>&copy; {{ date('Y') }} ISM System. All rights reserved.</span>
                    <span>v1.0.0</span>
                </div>
            </div>

            {{-- ===============================================================
             PART 4: Right Login Form Section (Responsive on Mobile & Desktop)
             =============================================================== --}}
            <div class="col-12 col-lg-6 d-flex flex-column justify-content-center align-items-center p-4 p-sm-5">

                <!-- Mobile Brand Header (Only visible on screens smaller than lg) -->
                <div class="d-flex d-lg-none align-items-center gap-2 mb-4 text-center">
                    <div class="rounded-3 bg-primary text-white d-inline-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                        <i class="bi bi-layers-half fs-5"></i>
                    </div>
                    <div class="text-start">
                        <h5 class="mb-0 fw-bold text-dark">ISM System</h5>
                        <small class="text-muted">Management Portal</small>
                    </div>
                </div>

                <!-- Main Login Card -->
                <div class="login-card p-4 p-sm-5">
                    <div class="mb-4">
                        <h3 class="fw-bold text-dark mb-1">Welcome Back</h3>
                        <p class="text-secondary small mb-0">Please enter your credentials to sign in to your account.</p>
                    </div>

                    {{-- =======================================================
                     PART 5: Backend Status & Validation Error Alerts
                     Source: app/Http/Controllers/Auth/LoginController.php
                     ======================================================= --}}
                    @if (session('status'))
                    <div class="alert alert-success alert-dismissible fade show py-2 small" role="alert">
                        <i class="bi bi-check-circle-fill me-1"></i> {{ session('status') }}
                        <button type="button" class="btn-close py-2" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    @endif

                    @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show py-2 small" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i> {{ $errors->first() }}
                        <button type="button" class="btn-close py-2" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    @endif

                    {{-- =======================================================
                     PART 6: Login Form
                     Submits POST request to:
                     - Route Name: 'login.submit' (routes/web.php)
                     - Controller Action: app/Http/Controllers/Auth/LoginController.php @ login()
                     ======================================================= --}}
                    <form action="{{ route('login.submit') }}" method="POST">
                        @csrf

                        <!-- Input 1: Email Address -->
                        <div class="mb-3">
                            <label for="email" class="form-label fw-medium small text-dark">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text border-end-0">
                                    <i class="bi bi-envelope"></i>
                                </span>
                                <input
                                    type="email"
                                    class="form-control border-start-0 ps-0 @error('email') is-invalid @enderror"
                                    id="email"
                                    name="email"
                                    value="{{ old('email', 'superadmin@ism.com') }}"
                                    placeholder="superadmin@ism.com"
                                    required
                                    autofocus>
                            </div>
                        </div>

                        <!-- Input 2: Password with Show/Hide Toggle -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <label for="password" class="form-label fw-medium small text-dark">Password</label>
                                <a href="#forgotPasswordModal"
                                   data-bs-toggle="modal"
                                   data-bs-target="#forgotPasswordModal"
                                   class="text-decoration-none small fw-medium text-primary">
                                    Forgot password?
                                </a>
                            </div>
                            <div class="input-group">
                                <span class="input-group-text border-end-0">
                                    <i class="bi bi-lock"></i>
                                </span>
                                <input
                                    type="password"
                                    class="form-control border-start-0 border-end-0 ps-0 @error('password') is-invalid @enderror"
                                    id="password"
                                    name="password"
                                    placeholder="Enter your password"
                                    required>
                                <button
                                    class="input-group-text bg-white border-start-0"
                                    type="button"
                                    id="togglePassword"
                                    aria-label="Toggle password visibility">
                                    <i class="bi bi-eye" id="togglePasswordIcon"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Input 3: Remember Me Checkbox -->
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="1" id="remember" name="remember" {{ old('remember') ? 'checked' : '' }}>
                                <label class="form-check-label small text-secondary" for="remember">
                                    Keep me signed in
                                </label>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-ism">
                                <i class="bi bi-box-arrow-in-right me-2"></i> Sign In
                            </button>
                        </div>
                    </form>

                    {{-- =======================================================
                     PART 7: All 4 System Role Credentials Box (Click to Auto-Fill)
                     Data Seeded In: database/seeders/DatabaseSeeder.php
                     User Model Roles: app/Models/User.php
                     ======================================================= --}}
                    <div class="bg-light border rounded-3 p-3 mt-3 small">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-semibold text-dark">
                                <i class="bi bi-key-fill text-primary me-1"></i> Demo Login Credentials:
                            </span>
                            <span class="text-muted" style="font-size: 0.72rem;">Click any role to auto-fill</span>
                        </div>

                        <div class="d-grid gap-2">
                            <!-- 1. Admin -->
                            <div class="cred-row rounded-2 p-2 d-flex justify-content-between align-items-center"
                                onclick="fillCredentials('superadmin@ism.com', 'Admin@123')">
                                <div>
                                    <span class="badge text-bg-primary me-1">Admin</span>
                                    <code class="text-dark">superadmin@ism.com</code> / <code class="text-dark">Admin@123</code>
                                </div>
                                <i class="bi bi-arrow-up-right-circle text-primary"></i>
                            </div>

                            <!-- 2. Store Manager -->
                            <div class="cred-row rounded-2 p-2 d-flex justify-content-between align-items-center"
                                onclick="fillCredentials('storemanager@ism.com', 'Manager@123')">
                                <div>
                                    <span class="badge text-bg-info me-1">Store Manager</span>
                                    <code class="text-dark">storemanager@ism.com</code> / <code class="text-dark">Manager@123</code>
                                </div>
                                <i class="bi bi-arrow-up-right-circle text-primary"></i>
                            </div>

                            <!-- 3. Stock Manager -->
                            <div class="cred-row rounded-2 p-2 d-flex justify-content-between align-items-center"
                                onclick="fillCredentials('stockmanager@ism.com', 'Stock@123')">
                                <div>
                                    <span class="badge text-bg-warning me-1">Stock Manager</span>
                                    <code class="text-dark">stockmanager@ism.com</code> / <code class="text-dark">Stock@123</code>
                                </div>
                                <i class="bi bi-arrow-up-right-circle text-primary"></i>
                            </div>

                            <!-- 4. Cashier -->
                            <div class="cred-row rounded-2 p-2 d-flex justify-content-between align-items-center"
                                onclick="fillCredentials('cashier@ism.com', 'Cashier@123')">
                                <div>
                                    <span class="badge text-bg-success me-1">Cashier</span>
                                    <code class="text-dark">cashier@ism.com</code> / <code class="text-dark">Cashier@123</code>
                                </div>
                                <i class="bi bi-arrow-up-right-circle text-primary"></i>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4 text-secondary opacity-25">

                    <div class="text-center">
                        <p class="small text-secondary mb-0">
                            Need help accessing your account?
                            <a href="#forgotPasswordModal"
                               data-bs-toggle="modal"
                               data-bs-target="#forgotPasswordModal"
                               class="text-primary fw-semibold text-decoration-none">
                                Contact Administrator
                            </a>
                        </p>
                    </div>
                </div>

                <!-- Mobile Footer -->
                <div class="d-lg-none text-center text-muted small mt-4">
                    &copy; {{ date('Y') }} ISM System. All rights reserved.
                </div>
            </div>

        </div>
    </div>

    {{-- =======================================================================
     PART 7B: Forgot Password / Contact System Administrator Modal
     Connected Backend Action: POST /forgot-password-request -> LoginController@requestPasswordReset
     Connected Admin Reset Page: GET /admin/users -> UserController@update (resources/views/admin/users/index.blade.php)
     ======================================================================= --}}
    <div class="modal fade" id="forgotPasswordModal" tabindex="-1" aria-labelledby="forgotPasswordModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="modal-header bg-dark text-white border-0 py-3 px-4">
                    <div class="d-flex align-items-center gap-2">
                        <div class="rounded-3 bg-primary bg-opacity-25 text-info d-inline-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                            <i class="bi bi-shield-lock-fill"></i>
                        </div>
                        <div>
                            <h6 class="modal-title fw-bold mb-0" id="forgotPasswordModalLabel">Forgot Password / Contact Administrator</h6>
                            <small class="text-white-50" style="font-size: 0.74rem;">{{ $storeName ?? 'ISM Retail & POS System' }}</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form action="{{ route('password.request.admin') }}" method="POST">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="alert alert-primary bg-primary bg-opacity-10 border-0 small text-dark mb-3">
                            <div class="fw-semibold mb-1">
                                <i class="bi bi-info-circle-fill text-primary me-1"></i> Enterprise Role-Based Password Security
                            </div>
                            For security reasons, staff passwords (<strong>Store Manager</strong>, <strong>Stock Manager</strong>, and <strong>Cashier</strong>) are managed and reset by the <strong>System Administrator</strong> via <code>User Management (/admin/users)</code>.
                        </div>

                        {{-- Direct Admin Support Contact Channels (Loaded from SystemSettings) --}}
                        <div class="row g-2 mb-3">
                            <div class="col-sm-6">
                                <a href="mailto:{{ $supportEmail ?? 'support@ism.com' }}?subject=ISM%20Password%20Reset%20Request"
                                   class="d-flex align-items-center gap-2 p-2 border rounded-3 text-decoration-none bg-light">
                                    <i class="bi bi-envelope-at-fill text-primary fs-5 ms-1"></i>
                                    <div class="overflow-hidden">
                                        <div class="text-secondary" style="font-size: 0.7rem;">Admin Support Email</div>
                                        <div class="fw-semibold text-dark small text-truncate">{{ $supportEmail ?? 'support@ism.com' }}</div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-sm-6">
                                <a href="tel:{{ preg_replace('/[^0-9+]/', '', $supportPhone ?? '+94112345678') }}"
                                   class="d-flex align-items-center gap-2 p-2 border rounded-3 text-decoration-none bg-light">
                                    <i class="bi bi-telephone-outbound-fill text-success fs-5 ms-1"></i>
                                    <div class="overflow-hidden">
                                        <div class="text-secondary" style="font-size: 0.7rem;">IT / Admin Hotline</div>
                                        <div class="fw-semibold text-dark small text-truncate">{{ $supportPhone ?? '+94 11 234 5678' }}</div>
                                    </div>
                                </a>
                            </div>
                        </div>

                        <hr class="my-3">

                        <div class="mb-3">
                            <label for="staff_email" class="form-label small fw-semibold text-dark">
                                Your Registered Login Email <span class="text-danger">*</span>
                            </label>
                            <input type="email" name="staff_email" id="staff_email" class="form-control form-control-sm"
                                   placeholder="e.g., cashier@ism.com" required>
                        </div>

                        <div class="mb-3">
                            <label for="staff_role" class="form-label small fw-semibold text-dark">
                                Your Assigned Role <span class="text-danger">*</span>
                            </label>
                            <select name="staff_role" id="staff_role" class="form-select form-select-sm" required>
                                <option value="Cashier">POS Cashier</option>
                                <option value="Stock Manager">Stock Manager</option>
                                <option value="Store Manager">Store Manager</option>
                                <option value="Admin">System Administrator</option>
                            </select>
                        </div>

                        <div class="mb-1">
                            <label for="reason_note" class="form-label small fw-semibold text-dark">
                                Note for System Administrator (Optional)
                            </label>
                            <textarea name="reason_note" id="reason_note" rows="2" class="form-control form-control-sm"
                                      placeholder="e.g., Forgot password at Counter #2 shift start, please reset."></textarea>
                        </div>
                    </div>

                    <div class="modal-footer bg-light border-0 py-3 px-4 d-flex justify-content-between">
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary btn-sm px-3 fw-semibold">
                            <i class="bi bi-send-check me-1"></i> Send Reset Request to Admin
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- =======================================================================
     PART 8: Bootstrap JS, Password Visibility Toggle & Quick Auto-Fill Script
     ======================================================================= --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle password input between 'password' and 'text'
        const togglePasswordBtn = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        const emailInput = document.getElementById('email');
        const togglePasswordIcon = document.getElementById('togglePasswordIcon');

        if (togglePasswordBtn) {
            togglePasswordBtn.addEventListener('click', function() {
                const isPassword = passwordInput.getAttribute('type') === 'password';
                passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
                togglePasswordIcon.classList.toggle('bi-eye', !isPassword);
                togglePasswordIcon.classList.toggle('bi-eye-slash', isPassword);
            });
        }

        // Auto-fill Email and Password when clicking any of the 4 Role rows in Part 7
        function fillCredentials(email, password) {
            if (emailInput && passwordInput) {
                emailInput.value = email;
                passwordInput.value = password;
                emailInput.focus();
            }
        }

        // Pre-fill the Forgot Password modal email input with whatever email the user typed on the login form
        const forgotModalEl = document.getElementById('forgotPasswordModal');
        const staffEmailInput = document.getElementById('staff_email');
        if (forgotModalEl && staffEmailInput && emailInput) {
            forgotModalEl.addEventListener('show.bs.modal', function() {
                if (!staffEmailInput.value && emailInput.value) {
                    staffEmailInput.value = emailInput.value;
                }
            });
        }
    </script>
</body>

</html>