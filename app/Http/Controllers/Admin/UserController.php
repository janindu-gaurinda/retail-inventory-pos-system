<?php

/**
 * ============================================================================
 * FILE PATH: app/Http/Controllers/Admin/UserController.php
 * DESCRIPTION: Module 11 — User Management Controller (Full CRUD, Role Assignment,
 *              Password Reset & Self-Deletion Protection).
 * ============================================================================
 *
 * ROLE ACCESS MATRIX FOR THIS MODULE (Module 11: User Management):
 * - Admin:         ✓ Full Access (Create, Edit, Change Role, Reset Password, Delete)
 * - Store Manager: ❌ No Access (403 Forbidden)
 * - Stock Manager: ❌ No Access (403 Forbidden)
 * - Cashier:       ❌ No Access (403 Forbidden)
 *
 * CONNECTED FILES:
 * - UI Blade View: resources/views/admin/users/index.blade.php
 * - Master Layout: resources/views/layouts/admin.blade.php
 * - Shared Sidebar: resources/views/partials/sidebar.blade.php
 * - User Model: app/Models/User.php
 * - Sale Model (Activity Count): app/Models/Sale.php
 * - StockAdjustment Model (Activity Count): app/Models/StockAdjustment.php
 * - PurchaseOrder Model (Activity Count): app/Models/PurchaseOrder.php
 * - Routes File: routes/web.php
 * ============================================================================
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\Sale;
use App\Models\StockAdjustment;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | PART 1: Display User Management Directory & Role Matrix (GET /admin/users)
    |--------------------------------------------------------------------------
    */
    public function index(Request $request): View
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();

        if (! $currentUser->canManageUsers()) {
            abort(403, 'Access denied. Only System Administrators can access User Management.');
        }

        // Filter parameters
        $search = trim((string) $request->query('search', ''));
        $roleFilter = trim((string) $request->query('role', ''));

        $usersQuery = User::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('role', 'like', "%{$search}%");
                });
            })
            ->when($roleFilter !== '', function ($query) use ($roleFilter) {
                if ($roleFilter === User::ROLE_ADMIN) {
                    $query->whereIn('role', [User::ROLE_ADMIN, 'super_admin']);
                } else {
                    $query->where('role', $roleFilter);
                }
            })
            ->orderByRaw("CASE
                WHEN role IN ('admin', 'super_admin') THEN 1
                WHEN role = 'store_manager' THEN 2
                WHEN role = 'stock_manager' THEN 3
                WHEN role = 'cashier' THEN 4
                ELSE 5
            END")
            ->orderBy('name');

        $users = $usersQuery->get();

        // Activity counts per user (Sales processed, Stock Adjustments requested, POs created)
        $salesCounts = Sale::query()
            ->selectRaw('user_id, COUNT(*) as count, COALESCE(SUM(grand_total), 0) as total_sales')
            ->where('status', 'completed')
            ->groupBy('user_id')
            ->get()
            ->keyBy('user_id');

        $adjustmentCounts = StockAdjustment::query()
            ->selectRaw('requested_by, COUNT(*) as count')
            ->groupBy('requested_by')
            ->pluck('count', 'requested_by');

        $poCounts = PurchaseOrder::query()
            ->selectRaw('created_by, COUNT(*) as count')
            ->groupBy('created_by')
            ->pluck('count', 'created_by');

        // Summary KPI counts across all users in the system
        $allUsers = User::all();
        $stats = [
            'total_users' => $allUsers->count(),
            'admins_count' => $allUsers->whereIn('role', [User::ROLE_ADMIN, 'super_admin'])->count(),
            'store_managers_count' => $allUsers->where('role', User::ROLE_STORE_MANAGER)->count(),
            'stock_managers_count' => $allUsers->where('role', User::ROLE_STOCK_MANAGER)->count(),
            'cashiers_count' => $allUsers->where('role', User::ROLE_CASHIER)->count(),
        ];

        return view('admin.users.index', [
            'users' => $users,
            'stats' => $stats,
            'search' => $search,
            'roleFilter' => $roleFilter,
            'salesCounts' => $salesCounts,
            'adjustmentCounts' => $adjustmentCounts,
            'poCounts' => $poCounts,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | PART 2: Create New Staff / System User (POST /admin/users)
    |--------------------------------------------------------------------------
    */
    public function store(Request $request): RedirectResponse
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();

        if (! $currentUser->canManageUsers()) {
            abort(403, 'Access denied. Only System Administrators can create user accounts.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email'],
            'role' => [
                'required',
                Rule::in([
                    User::ROLE_ADMIN,
                    User::ROLE_STORE_MANAGER,
                    User::ROLE_STOCK_MANAGER,
                    User::ROLE_CASHIER,
                ]),
            ],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $newUser = User::create([
            'name' => $validated['name'],
            'email' => strtolower(trim($validated['email'])),
            'role' => $validated['role'],
            'password' => Hash::make($validated['password']),
            'email_verified_at' => now(),
        ]);

        return redirect()
            ->route('admin.users.index')
            ->with('success', "User account for \"{$newUser->name}\" ({$newUser->roleLabel()}) created successfully.");
    }

    /*
    |--------------------------------------------------------------------------
    | PART 3: Update Existing User Profile, Role & Optional Password (PUT /admin/users/{user})
    |--------------------------------------------------------------------------
    */
    public function update(Request $request, User $user): RedirectResponse
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();

        if (! $currentUser->canManageUsers()) {
            abort(403, 'Access denied. Only System Administrators can update user accounts.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => [
                'required',
                'email',
                'max:150',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'role' => [
                'required',
                Rule::in([
                    User::ROLE_ADMIN,
                    User::ROLE_STORE_MANAGER,
                    User::ROLE_STOCK_MANAGER,
                    User::ROLE_CASHIER,
                ]),
            ],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
        ]);

        // Prevent the currently logged-in Admin from demoting their own account out of Admin role
        if ($user->id === $currentUser->id && $validated['role'] !== User::ROLE_ADMIN) {
            return redirect()
                ->route('admin.users.index')
                ->with('error', 'Safety protection: You cannot remove the Admin role from your own active account.');
        }

        // Prevent demoting the last remaining Admin in the system
        if ($user->isAdmin() && $validated['role'] !== User::ROLE_ADMIN) {
            $remainingAdmins = User::whereIn('role', [User::ROLE_ADMIN, 'super_admin'])
                ->where('id', '!=', $user->id)
                ->count();

            if ($remainingAdmins === 0) {
                return redirect()
                    ->route('admin.users.index')
                    ->with('error', 'Safety protection: The system must have at least one Admin user.');
            }
        }

        $user->name = $validated['name'];
        $user->email = strtolower(trim($validated['email']));
        $user->role = $validated['role'];

        $passwordChanged = false;
        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
            $passwordChanged = true;
        }

        $user->save();

        $msg = "User \"{$user->name}\" ({$user->roleLabel()}) updated successfully.";
        if ($passwordChanged) {
            $msg .= ' Password has also been reset.';
        }

        return redirect()
            ->route('admin.users.index')
            ->with('success', $msg);
    }

    /*
    |--------------------------------------------------------------------------
    | PART 4: Delete User Account (DELETE /admin/users/{user})
    |--------------------------------------------------------------------------
    */
    public function destroy(User $user): RedirectResponse
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();

        if (! $currentUser->canManageUsers()) {
            abort(403, 'Access denied. Only System Administrators can delete user accounts.');
        }

        // Prevent self-deletion
        if ($user->id === $currentUser->id) {
            return redirect()
                ->route('admin.users.index')
                ->with('error', 'Safety protection: You cannot delete your own currently logged-in account.');
        }

        // Prevent deleting the last remaining Admin
        if ($user->isAdmin()) {
            $remainingAdmins = User::whereIn('role', [User::ROLE_ADMIN, 'super_admin'])
                ->where('id', '!=', $user->id)
                ->count();

            if ($remainingAdmins === 0) {
                return redirect()
                    ->route('admin.users.index')
                    ->with('error', 'Safety protection: You cannot delete the last remaining Admin account.');
            }
        }

        $deletedName = $user->name;
        $deletedRole = $user->roleLabel();
        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('success', "User account \"{$deletedName}\" ({$deletedRole}) has been deleted.");
    }
}
