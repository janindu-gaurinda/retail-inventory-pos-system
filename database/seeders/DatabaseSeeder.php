<?php

/**
 * ============================================================================
 * FILE PATH: database/seeders/DatabaseSeeder.php
 * DESCRIPTION: Cleans all catalog & transactional tables (keeping the 'users'
 *              table intact) and seeds a comprehensive 45-Day Bookshop &
 *              Stationery Store dataset across Categories, Suppliers, Products,
 *              Purchase Orders, Stock Adjustments, and POS Sales Invoices.
 * ============================================================================
 *
 * CONNECTED FILES:
 * - User Model (Preserved): app/Models/User.php
 * - Category Model: app/Models/Category.php
 * - Supplier Model: app/Models/Supplier.php
 * - Product Model: app/Models/Product.php
 * - PurchaseOrder Model: app/Models/PurchaseOrder.php
 * - StockAdjustment Model: app/Models/StockAdjustment.php
 * - Sale & SaleItem Models: app/Models/Sale.php & app/Models/SaleItem.php
 * - SystemSetting Model: app/Models/SystemSetting.php
 * ============================================================================
 */

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockAdjustment;
use App\Models\Supplier;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Clean all tables except 'users' and seed a 45-day Bookshop dataset.
     */
    public function run(): void
    {
        /*
        |----------------------------------------------------------------------
        | PART 1: Ensure All 4 System Role Users Exist (DO NOT CLEAN 'users' TABLE)
        |----------------------------------------------------------------------
        */
        $defaultUsers = [
            [
                'name' => 'Super Admin',
                'email' => 'superadmin@ism.com',
                'role' => User::ROLE_ADMIN,
                'password' => Hash::make('Admin@123'),
            ],
            [
                'name' => 'Store Manager',
                'email' => 'storemanager@ism.com',
                'role' => User::ROLE_STORE_MANAGER,
                'password' => Hash::make('Manager@123'),
            ],
            [
                'name' => 'Stock Manager',
                'email' => 'stockmanager@ism.com',
                'role' => User::ROLE_STOCK_MANAGER,
                'password' => Hash::make('Stock@123'),
            ],
            [
                'name' => 'Main Cashier',
                'email' => 'cashier@ism.com',
                'role' => User::ROLE_CASHIER,
                'password' => Hash::make('Cashier@123'),
            ],
        ];

        foreach ($defaultUsers as $u) {
            User::firstOrCreate(
                ['email' => $u['email']],
                array_merge($u, ['email_verified_at' => now()])
            );
        }

        $adminUser = User::where('role', User::ROLE_ADMIN)->first() ?? User::first();
        $storeManager = User::where('role', User::ROLE_STORE_MANAGER)->first() ?? $adminUser;
        $stockManager = User::where('role', User::ROLE_STOCK_MANAGER)->first() ?? $adminUser;
        $cashierUser = User::where('role', User::ROLE_CASHIER)->first() ?? $adminUser;

        /*
        |----------------------------------------------------------------------
        | PART 2: Clean All Other Tables & Reset Auto-Increment IDs (Except 'users')
        |----------------------------------------------------------------------
        */
        Schema::disableForeignKeyConstraints();

        SaleItem::query()->delete();
        Sale::query()->delete();
        PurchaseOrder::query()->delete();
        StockAdjustment::query()->delete();
        Product::query()->delete();
        Supplier::query()->delete();
        Category::query()->delete();

        DB::statement("DELETE FROM sqlite_sequence WHERE name IN ('sale_items', 'sales', 'purchase_orders', 'stock_adjustments', 'products', 'suppliers', 'categories')");

        Schema::enableForeignKeyConstraints();

        /*
        |----------------------------------------------------------------------
        | PART 3: Ensure Bookshop System Settings Exist (Preserving Custom Store Name if Set)
        |----------------------------------------------------------------------
        */
        SystemSetting::ensureDefaultsExist();

        /*
        |----------------------------------------------------------------------
        | PART 4: Seed Bookshop Product Categories (6 Categories)
        |----------------------------------------------------------------------
        */
        $categoriesData = [
            [
                'category_code' => 'CAT-101',
                'name' => 'Exercise & CR Books',
                'description' => 'Single-rule, square-rule, and hard-cover CR exercise books for school and office use.',
                'status' => 'active',
            ],
            [
                'category_code' => 'CAT-102',
                'name' => 'School & Exam Textbooks',
                'description' => 'G.C.E. O/L, A/L, Grade 5 Scholarship past paper books, educational guides, and dictionaries.',
                'status' => 'active',
            ],
            [
                'category_code' => 'CAT-103',
                'name' => 'Novels & General Reading',
                'description' => 'Sinhala & English fiction novels, translations, biographies, and children story books.',
                'status' => 'active',
            ],
            [
                'category_code' => 'CAT-104',
                'name' => 'Pens, Pencils & Writing',
                'description' => 'Ballpoint pens, gel pens, HB pencils, highlighters, permanent markers, and correction pens.',
                'status' => 'active',
            ],
            [
                'category_code' => 'CAT-105',
                'name' => 'Art, Craft & Geometry',
                'description' => 'Mathematical instrument boxes, watercolors, pastels, drawing blocks, and craft supplies.',
                'status' => 'active',
            ],
            [
                'category_code' => 'CAT-106',
                'name' => 'Office Stationery & Paper',
                'description' => 'A4 80GSM photocopy paper reams, box files, staplers, calculators, and sticky notes.',
                'status' => 'active',
            ],
        ];

        foreach ($categoriesData as $catData) {
            Category::create($catData);
        }

        /*
        |----------------------------------------------------------------------
        | PART 5: Seed Bookshop & Stationery Suppliers (5 Suppliers)
        |----------------------------------------------------------------------
        */
        $suppliersData = [
            [
                'supplier_code' => 'SUP-101',
                'company_name' => 'Atlas Axillia Co. (Pvt) Ltd',
                'contact_person' => 'Nuwan Jayasuriya',
                'email' => 'orders@atlas.lk',
                'phone' => '+94 11 532 0320',
                'category' => 'Exercise & CR Books',
                'city' => 'Peliyagoda',
                'address' => 'No. 96, Parakrama Road, Peliyagoda',
                'tax_number' => 'VAT-1029384-1',
                'payment_terms' => 'Net 30',
                'outstanding_balance' => 145000.00,
                'status' => 'active',
            ],
            [
                'supplier_code' => 'SUP-102',
                'company_name' => 'Sarasavi Book Publishers',
                'contact_person' => 'Chaminda Rathnayake',
                'email' => 'wholesale@sarasavi.lk',
                'phone' => '+94 11 282 0820',
                'category' => 'Novels & General Reading',
                'city' => 'Nugegoda',
                'address' => 'No. 30, Stanley Thilakaratne Mawatha, Nugegoda',
                'tax_number' => 'VAT-4091823-7',
                'payment_terms' => 'Net 30',
                'outstanding_balance' => 88500.00,
                'status' => 'active',
            ],
            [
                'supplier_code' => 'SUP-103',
                'company_name' => 'M.D. Gunasena Educational Distributors',
                'contact_person' => 'Dilshan Wijesinghe',
                'email' => 'distribution@mdgunasena.com',
                'phone' => '+94 11 232 3981',
                'category' => 'School & Exam Textbooks',
                'city' => 'Colombo 11',
                'address' => 'No. 217, Olcott Mawatha, Colombo 11',
                'tax_number' => 'VAT-7712904-2',
                'payment_terms' => 'Net 15',
                'outstanding_balance' => 62000.00,
                'status' => 'active',
            ],
            [
                'supplier_code' => 'SUP-104',
                'company_name' => 'Richard Trading & Stationery Lankem',
                'contact_person' => 'Thilina Bandara',
                'email' => 'sales@richardstationery.lk',
                'phone' => '+94 11 479 2000',
                'category' => 'Office Stationery & Paper',
                'city' => 'Wattala',
                'address' => 'No. 380, Negombo Road, Wattala',
                'tax_number' => 'VAT-5510928-9',
                'payment_terms' => 'Net 30',
                'outstanding_balance' => 112000.00,
                'status' => 'active',
            ],
            [
                'supplier_code' => 'SUP-105',
                'company_name' => 'Promate Stationery (Printxcel)',
                'contact_person' => 'Kaveesha Perera',
                'email' => 'trade@promateworld.com',
                'phone' => '+94 11 461 5615',
                'category' => 'Art, Craft & Geometry',
                'city' => 'Maharagama',
                'address' => 'High Level Road, Maharagama',
                'tax_number' => 'VAT-9901827-4',
                'payment_terms' => 'Cash on Delivery',
                'outstanding_balance' => 0.00,
                'status' => 'active',
            ],
        ];

        $suppliersByCode = [];
        foreach ($suppliersData as $supData) {
            $suppliersByCode[$supData['supplier_code']] = Supplier::create($supData);
        }

        /*
        |----------------------------------------------------------------------
        | PART 6: Seed 12 Bookshop & Stationery Products
        |----------------------------------------------------------------------
        */
        $productsData = [
            [
                'sku' => 'BKS-CR-001',
                'barcode' => '9789550001019',
                'name' => 'Atlas CR Book 120 Pages Single Rule (A4)',
                'category' => 'Exercise & CR Books',
                'supplier' => 'Atlas Axillia Co. (Pvt) Ltd',
                'unit' => 'pcs',
                'cost_price' => 230.00,
                'selling_price' => 320.00,
                'stock_quantity' => 140,
                'min_stock_level' => 30,
                'status' => 'active',
                'description' => 'Hard cover A4 120-page single rule CR book with bright 70GSM paper.',
            ],
            [
                'sku' => 'BKS-CR-002',
                'barcode' => '9789550001026',
                'name' => 'Promate CR Book 200 Pages Single Rule',
                'category' => 'Exercise & CR Books',
                'supplier' => 'Promate Stationery (Printxcel)',
                'unit' => 'pcs',
                'cost_price' => 350.00,
                'selling_price' => 480.00,
                'stock_quantity' => 85,
                'min_stock_level' => 25,
                'status' => 'active',
                'description' => 'Premium laminated 200-page single rule CR book for university & A/L students.',
            ],
            [
                'sku' => 'BKS-EX-003',
                'barcode' => '9789550001033',
                'name' => 'Atlas Exercise Book 80 Pages Single Rule (Pack of 10)',
                'category' => 'Exercise & CR Books',
                'supplier' => 'Atlas Axillia Co. (Pvt) Ltd',
                'unit' => 'pack',
                'cost_price' => 880.00,
                'selling_price' => 1200.00,
                'stock_quantity' => 8, // Low stock alert item
                'min_stock_level' => 15,
                'status' => 'active',
                'description' => 'Bundle pack of 10 school exercise books (80 pages each).',
            ],
            [
                'sku' => 'BKS-TX-004',
                'barcode' => '9789550001040',
                'name' => 'Oxford Advanced Learner\'s Dictionary (10th Edition)',
                'category' => 'School & Exam Textbooks',
                'supplier' => 'M.D. Gunasena Educational Distributors',
                'unit' => 'pcs',
                'cost_price' => 2400.00,
                'selling_price' => 3250.00,
                'stock_quantity' => 22,
                'min_stock_level' => 8,
                'status' => 'active',
                'description' => 'Original paperback dictionary with over 60,000 words and idioms.',
            ],
            [
                'sku' => 'BKS-TX-005',
                'barcode' => '9789550001057',
                'name' => 'G.C.E. O/L Mathematics Past Papers & Model Answers (2016-2025)',
                'category' => 'School & Exam Textbooks',
                'supplier' => 'M.D. Gunasena Educational Distributors',
                'unit' => 'pcs',
                'cost_price' => 620.00,
                'selling_price' => 890.00,
                'stock_quantity' => 45,
                'min_stock_level' => 15,
                'status' => 'active',
                'description' => 'Complete 10-year O/L Mathematics past paper compilation with marking schemes.',
            ],
            [
                'sku' => 'BKS-NV-006',
                'barcode' => '9789550001064',
                'name' => 'Gamperaliya — Martin Wickramasinghe (Classic Novel)',
                'category' => 'Novels & General Reading',
                'supplier' => 'Sarasavi Book Publishers',
                'unit' => 'pcs',
                'cost_price' => 520.00,
                'selling_price' => 750.00,
                'stock_quantity' => 32,
                'min_stock_level' => 10,
                'status' => 'active',
                'description' => 'Bestselling Sinhala classic literature novel by Martin Wickramasinghe.',
            ],
            [
                'sku' => 'BKS-NV-007',
                'barcode' => '9789550001071',
                'name' => 'Sherlock Holmes Complete Casebook (Sinhala Translation)',
                'category' => 'Novels & General Reading',
                'supplier' => 'Sarasavi Book Publishers',
                'unit' => 'pcs',
                'cost_price' => 680.00,
                'selling_price' => 950.00,
                'stock_quantity' => 5, // Low stock alert item
                'min_stock_level' => 12,
                'status' => 'active',
                'description' => 'Popular detective novel translation by Chandana Mendis.',
            ],
            [
                'sku' => 'BKS-PN-008',
                'barcode' => '9789550001088',
                'name' => 'Atlas Chooty Ballpoint Pen Blue (Box of 50)',
                'category' => 'Pens, Pencils & Writing',
                'supplier' => 'Atlas Axillia Co. (Pvt) Ltd',
                'unit' => 'box',
                'cost_price' => 950.00,
                'selling_price' => 1350.00,
                'stock_quantity' => 60,
                'min_stock_level' => 20,
                'status' => 'active',
                'description' => 'Smooth 0.7mm tip blue ballpoint pen retail counter box of 50 pens.',
            ],
            [
                'sku' => 'BKS-PN-009',
                'barcode' => '9789550001095',
                'name' => 'Faber-Castell Neon Highlighter Set (Pack of 4)',
                'category' => 'Pens, Pencils & Writing',
                'supplier' => 'Richard Trading & Stationery Lankem',
                'unit' => 'pack',
                'cost_price' => 440.00,
                'selling_price' => 650.00,
                'stock_quantity' => 38,
                'min_stock_level' => 12,
                'status' => 'active',
                'description' => 'Assorted neon yellow, green, pink, and orange chisel-tip highlighters.',
            ],
            [
                'sku' => 'BKS-AR-010',
                'barcode' => '9789550001101',
                'name' => 'Nataraj Mathematical Drawing Instruments Tin Box',
                'category' => 'Art, Craft & Geometry',
                'supplier' => 'Promate Stationery (Printxcel)',
                'unit' => 'pcs',
                'cost_price' => 380.00,
                'selling_price' => 550.00,
                'stock_quantity' => 52,
                'min_stock_level' => 15,
                'status' => 'active',
                'description' => 'Complete school geometry box with compass, divider, protractor, and set squares.',
            ],
            [
                'sku' => 'BKS-OF-011',
                'barcode' => '9789550001118',
                'name' => 'Double A 80GSM A4 Photocopy Paper Ream (500 Sheets)',
                'category' => 'Office Stationery & Paper',
                'supplier' => 'Richard Trading & Stationery Lankem',
                'unit' => 'pcs',
                'cost_price' => 1850.00,
                'selling_price' => 2350.00,
                'stock_quantity' => 75,
                'min_stock_level' => 25,
                'status' => 'active',
                'description' => 'High-whiteness jam-free 80GSM A4 paper ream for laser and inkjet printing.',
            ],
            [
                'sku' => 'BKS-OF-012',
                'barcode' => '9789550001125',
                'name' => 'Casio Scientific Calculator FX-991EX ClassWiz',
                'category' => 'Office Stationery & Paper',
                'supplier' => 'Richard Trading & Stationery Lankem',
                'unit' => 'pcs',
                'cost_price' => 4800.00,
                'selling_price' => 6400.00,
                'stock_quantity' => 0, // Out of stock item
                'min_stock_level' => 6,
                'status' => 'active',
                'description' => '552-function non-programmable scientific calculator for A/L & engineering exams.',
            ],
        ];

        $productsBySku = [];
        foreach ($productsData as $prodData) {
            $productsBySku[$prodData['sku']] = Product::create($prodData);
        }

        /*
        |----------------------------------------------------------------------
        | PART 7: Seed Purchase Orders Across the Last 45 Days (6 POs)
        |----------------------------------------------------------------------
        */
        $poRecords = [
            [
                'po_number' => 'PO-2026-001',
                'supplier' => $suppliersByCode['SUP-101'],
                'product' => $productsBySku['BKS-CR-001'],
                'quantity' => 200,
                'unit_cost' => 230.00,
                'days_ago' => 42,
                'expected_offset' => -38,
                'status' => 'received',
                'notes' => 'Back-to-school bulk CR book restock received in full.',
            ],
            [
                'po_number' => 'PO-2026-002',
                'supplier' => $suppliersByCode['SUP-104'],
                'product' => $productsBySku['BKS-OF-011'],
                'quantity' => 100,
                'unit_cost' => 1850.00,
                'days_ago' => 31,
                'expected_offset' => -27,
                'status' => 'received',
                'notes' => 'Monthly A4 paper pallet delivery for retail & print counter.',
            ],
            [
                'po_number' => 'PO-2026-003',
                'supplier' => $suppliersByCode['SUP-102'],
                'product' => $productsBySku['BKS-NV-006'],
                'quantity' => 50,
                'unit_cost' => 520.00,
                'days_ago' => 21,
                'expected_offset' => -17,
                'status' => 'received',
                'notes' => 'Literary festival display stock.',
            ],
            [
                'po_number' => 'PO-2026-004',
                'supplier' => $suppliersByCode['SUP-103'],
                'product' => $productsBySku['BKS-TX-005'],
                'quantity' => 60,
                'unit_cost' => 620.00,
                'days_ago' => 12,
                'expected_offset' => -8,
                'status' => 'received',
                'notes' => 'O/L exam season past paper books.',
            ],
            [
                'po_number' => 'PO-2026-005',
                'supplier' => $suppliersByCode['SUP-104'],
                'product' => $productsBySku['BKS-OF-012'],
                'quantity' => 15,
                'unit_cost' => 4800.00,
                'days_ago' => 3,
                'expected_offset' => 4,
                'status' => 'ordered',
                'notes' => 'Urgent restock for out-of-stock Casio FX-991EX calculators.',
            ],
            [
                'po_number' => 'PO-2026-006',
                'supplier' => $suppliersByCode['SUP-101'],
                'product' => $productsBySku['BKS-EX-003'],
                'quantity' => 40,
                'unit_cost' => 880.00,
                'days_ago' => 1,
                'expected_offset' => 5,
                'status' => 'ordered',
                'notes' => 'Replenishing 80-page exercise book 10-packs.',
            ],
        ];

        foreach ($poRecords as $poItem) {
            $createdAt = now()->subDays($poItem['days_ago']);
            $po = PurchaseOrder::create([
                'po_number' => $poItem['po_number'],
                'supplier_id' => $poItem['supplier']->id,
                'product_id' => $poItem['product']->id,
                'quantity' => $poItem['quantity'],
                'unit_cost' => $poItem['unit_cost'],
                'total_amount' => round($poItem['quantity'] * $poItem['unit_cost'], 2),
                'expected_date' => now()->addDays($poItem['expected_offset'])->toDateString(),
                'status' => $poItem['status'],
                'notes' => $poItem['notes'],
                'created_by' => $stockManager->id,
                'received_by' => $poItem['status'] === 'received' ? $storeManager->id : null,
                'received_at' => $poItem['status'] === 'received' ? $createdAt->copy()->addDays(3) : null,
            ]);
            $po->timestamps = false;
            $po->created_at = $createdAt;
            $po->updated_at = $createdAt;
            $po->save();
        }

        /*
        |----------------------------------------------------------------------
        | PART 8: Seed Stock Adjustments Across the Last 40 Days (5 Adjustments)
        |----------------------------------------------------------------------
        */
        $adjustmentsData = [
            [
                'reference_no' => 'ADJ-2026-001',
                'product' => $productsBySku['BKS-CR-001'],
                'adjustment_type' => 'subtraction',
                'quantity' => 4,
                'previous_stock' => 144,
                'new_stock' => 140,
                'reason' => 'Damaged / Defective Goods',
                'notes' => 'Bent corners on 4 CR books from bottom carton.',
                'status' => 'approved',
                'days_ago' => 36,
            ],
            [
                'reference_no' => 'ADJ-2026-002',
                'product' => $productsBySku['BKS-PN-008'],
                'adjustment_type' => 'addition',
                'quantity' => 3,
                'previous_stock' => 57,
                'new_stock' => 60,
                'reason' => 'Physical Audit Recount',
                'notes' => 'Found 3 unopened Atlas Chooty pen boxes during monthly stock take.',
                'status' => 'approved',
                'days_ago' => 24,
            ],
            [
                'reference_no' => 'ADJ-2026-003',
                'product' => $productsBySku['BKS-OF-011'],
                'adjustment_type' => 'subtraction',
                'quantity' => 2,
                'previous_stock' => 77,
                'new_stock' => 75,
                'reason' => 'Sample / Internal Use',
                'notes' => 'Allocated 2 Double A paper reams for bookshop office & invoice printing.',
                'status' => 'approved',
                'days_ago' => 14,
            ],
            [
                'reference_no' => 'ADJ-2026-004',
                'product' => $productsBySku['BKS-NV-007'],
                'adjustment_type' => 'subtraction',
                'quantity' => 2,
                'previous_stock' => 7,
                'new_stock' => 5,
                'reason' => 'Damaged / Defective Goods',
                'notes' => 'Misprinted pages reported on 2 Sherlock Holmes copies; awaiting publisher return.',
                'status' => 'pending',
                'days_ago' => 2,
            ],
            [
                'reference_no' => 'ADJ-2026-005',
                'product' => $productsBySku['BKS-AR-010'],
                'adjustment_type' => 'addition',
                'quantity' => 5,
                'previous_stock' => 52,
                'new_stock' => 57,
                'reason' => 'Physical Audit Recount',
                'notes' => '5 bonus geometry boxes included by supplier promotion.',
                'status' => 'pending',
                'days_ago' => 1,
            ],
        ];

        foreach ($adjustmentsData as $adjItem) {
            $adjDate = now()->subDays($adjItem['days_ago']);
            $adj = StockAdjustment::create([
                'reference_no' => $adjItem['reference_no'],
                'product_id' => $adjItem['product']->id,
                'adjustment_type' => $adjItem['adjustment_type'],
                'quantity' => $adjItem['quantity'],
                'previous_stock' => $adjItem['previous_stock'],
                'new_stock' => $adjItem['new_stock'],
                'reason' => $adjItem['reason'],
                'notes' => $adjItem['notes'],
                'status' => $adjItem['status'],
                'requested_by' => $stockManager->id,
                'approved_by' => $adjItem['status'] === 'approved' ? $adminUser->id : null,
                'approved_at' => $adjItem['status'] === 'approved' ? $adjDate->copy()->addHours(4) : null,
            ]);
            $adj->timestamps = false;
            $adj->created_at = $adjDate;
            $adj->updated_at = $adjDate;
            $adj->save();
        }

        /*
        |----------------------------------------------------------------------
        | PART 9: Seed 16 Bookshop POS Sales Invoices Spread Over 45 Days
        |         (Includes Today, Last 7 Days, This Month, and 30-45 Days Ago)
        |----------------------------------------------------------------------
        */
        $salesBlueprints = [
            [
                'invoice_no' => 'INV-2026-1001',
                'days_ago' => 44,
                'user' => $cashierUser,
                'customer_name' => 'Walk-in Customer (In-Store)',
                'customer_phone' => '+94 77 210 4411',
                'payment_method' => 'Cash',
                'discount_amount' => 0.00,
                'items' => [
                    ['sku' => 'BKS-CR-001', 'qty' => 5],
                    ['sku' => 'BKS-PN-008', 'qty' => 1],
                ],
            ],
            [
                'invoice_no' => 'INV-2026-1002',
                'days_ago' => 39,
                'user' => $cashierUser,
                'customer_name' => 'Royal College Book Club',
                'customer_phone' => '+94 11 269 1029',
                'payment_method' => 'Card',
                'discount_amount' => 500.00,
                'items' => [
                    ['sku' => 'BKS-TX-004', 'qty' => 3],
                    ['sku' => 'BKS-NV-006', 'qty' => 4],
                ],
            ],
            [
                'invoice_no' => 'INV-2026-1003',
                'days_ago' => 34,
                'user' => $storeManager,
                'customer_name' => 'Wholesale / Trade Buyer',
                'customer_phone' => '+94 71 889 2200',
                'payment_method' => 'QR / Bank',
                'discount_amount' => 1200.00,
                'items' => [
                    ['sku' => 'BKS-OF-011', 'qty' => 10],
                    ['sku' => 'BKS-PN-008', 'qty' => 4],
                ],
            ],
            [
                'invoice_no' => 'INV-2026-1004',
                'days_ago' => 28,
                'user' => $cashierUser,
                'customer_name' => 'Online Order (Website / Social)',
                'customer_phone' => '+94 76 541 9988',
                'payment_method' => 'Card',
                'discount_amount' => 150.00,
                'items' => [
                    ['sku' => 'BKS-NV-006', 'qty' => 2],
                    ['sku' => 'BKS-NV-007', 'qty' => 2],
                ],
            ],
            [
                'invoice_no' => 'INV-2026-1005',
                'days_ago' => 24,
                'user' => $cashierUser,
                'customer_name' => 'Walk-in Customer (In-Store)',
                'customer_phone' => null,
                'payment_method' => 'Cash',
                'discount_amount' => 0.00,
                'items' => [
                    ['sku' => 'BKS-EX-003', 'qty' => 3],
                    ['sku' => 'BKS-AR-010', 'qty' => 2],
                ],
            ],
            [
                'invoice_no' => 'INV-2026-1006',
                'days_ago' => 19,
                'user' => $storeManager,
                'customer_name' => 'Corporate Account Customer',
                'customer_phone' => '+94 11 450 2211',
                'payment_method' => 'QR / Bank',
                'discount_amount' => 800.00,
                'items' => [
                    ['sku' => 'BKS-OF-011', 'qty' => 6],
                    ['sku' => 'BKS-PN-009', 'qty' => 5],
                ],
            ],
            [
                'invoice_no' => 'INV-2026-1007',
                'days_ago' => 15,
                'user' => $cashierUser,
                'customer_name' => 'Walk-in Customer (In-Store)',
                'customer_phone' => '+94 77 901 3344',
                'payment_method' => 'Cash',
                'discount_amount' => 100.00,
                'items' => [
                    ['sku' => 'BKS-TX-005', 'qty' => 4],
                    ['sku' => 'BKS-CR-002', 'qty' => 5],
                ],
            ],
            [
                'invoice_no' => 'INV-2026-1008',
                'days_ago' => 11,
                'user' => $cashierUser,
                'customer_name' => 'Phone / WhatsApp Delivery Order',
                'customer_phone' => '+94 70 312 7788',
                'payment_method' => 'QR / Bank',
                'discount_amount' => 0.00,
                'items' => [
                    ['sku' => 'BKS-OF-012', 'qty' => 1],
                    ['sku' => 'BKS-CR-002', 'qty' => 4],
                ],
            ],
            [
                'invoice_no' => 'INV-2026-1009',
                'days_ago' => 8,
                'user' => $cashierUser,
                'customer_name' => 'Walk-in Customer (In-Store)',
                'customer_phone' => null,
                'payment_method' => 'Cash',
                'discount_amount' => 0.00,
                'items' => [
                    ['sku' => 'BKS-CR-001', 'qty' => 10],
                    ['sku' => 'BKS-PN-009', 'qty' => 2],
                ],
            ],
            [
                'invoice_no' => 'INV-2026-1010',
                'days_ago' => 6,
                'user' => $storeManager,
                'customer_name' => 'Apex Tuition Academy',
                'customer_phone' => '+94 11 288 4455',
                'payment_method' => 'Card',
                'discount_amount' => 650.00,
                'items' => [
                    ['sku' => 'BKS-TX-005', 'qty' => 10],
                    ['sku' => 'BKS-PN-008', 'qty' => 2],
                ],
            ],
            [
                'invoice_no' => 'INV-2026-1011',
                'days_ago' => 4,
                'user' => $cashierUser,
                'customer_name' => 'Online Order (Website / Social)',
                'customer_phone' => '+94 77 654 1199',
                'payment_method' => 'Card',
                'discount_amount' => 250.00,
                'items' => [
                    ['sku' => 'BKS-TX-004', 'qty' => 1],
                    ['sku' => 'BKS-NV-007', 'qty' => 2],
                ],
            ],
            [
                'invoice_no' => 'INV-2026-1012',
                'days_ago' => 3,
                'user' => $cashierUser,
                'customer_name' => 'Walk-in Customer (In-Store)',
                'customer_phone' => null,
                'payment_method' => 'Cash',
                'discount_amount' => 0.00,
                'items' => [
                    ['sku' => 'BKS-AR-010', 'qty' => 4],
                    ['sku' => 'BKS-EX-003', 'qty' => 2],
                ],
            ],
            [
                'invoice_no' => 'INV-2026-1013',
                'days_ago' => 2,
                'user' => $cashierUser,
                'customer_name' => 'Wholesale / Trade Buyer',
                'customer_phone' => '+94 71 445 6677',
                'payment_method' => 'QR / Bank',
                'discount_amount' => 500.00,
                'items' => [
                    ['sku' => 'BKS-OF-011', 'qty' => 5],
                    ['sku' => 'BKS-CR-001', 'qty' => 15],
                ],
            ],
            [
                'invoice_no' => 'INV-2026-1014',
                'days_ago' => 1,
                'user' => $cashierUser,
                'customer_name' => 'Walk-in Customer (In-Store)',
                'customer_phone' => '+94 77 123 9876',
                'payment_method' => 'Cash',
                'discount_amount' => 140.00,
                'items' => [
                    ['sku' => 'BKS-NV-006', 'qty' => 2],
                    ['sku' => 'BKS-CR-002', 'qty' => 3],
                ],
            ],
            // Today's Sales (0 days ago)
            [
                'invoice_no' => 'INV-2026-1015',
                'days_ago' => 0,
                'user' => $cashierUser,
                'customer_name' => 'Walk-in Customer (In-Store)',
                'customer_phone' => '+94 77 555 8822',
                'payment_method' => 'Cash',
                'discount_amount' => 100.00,
                'items' => [
                    ['sku' => 'BKS-CR-001', 'qty' => 6],
                    ['sku' => 'BKS-TX-005', 'qty' => 2],
                    ['sku' => 'BKS-PN-009', 'qty' => 2],
                ],
            ],
            [
                'invoice_no' => 'INV-2026-1016',
                'days_ago' => 0,
                'user' => $storeManager,
                'customer_name' => 'Online Order (Website / Social)',
                'customer_phone' => '+94 71 998 1122',
                'payment_method' => 'Card',
                'discount_amount' => 200.00,
                'items' => [
                    ['sku' => 'BKS-TX-004', 'qty' => 1],
                    ['sku' => 'BKS-OF-011', 'qty' => 2],
                ],
            ],
        ];

        foreach ($salesBlueprints as $sb) {
            $saleDate = $sb['days_ago'] === 0
                ? now()->subMinutes(rand(15, 180))
                : now()->subDays($sb['days_ago'])->setTime(rand(9, 18), rand(0, 59));

            $subtotal = 0.0;
            $itemsCount = 0;
            $linesToCreate = [];

            foreach ($sb['items'] as $line) {
                $prod = $productsBySku[$line['sku']];
                $qty = $line['qty'];
                $unitPrice = (float) $prod->selling_price;
                $lineTotal = round($unitPrice * $qty, 2);

                $subtotal += $lineTotal;
                $itemsCount += $qty;

                $linesToCreate[] = [
                    'product_id' => $prod->id,
                    'product_name' => $prod->name,
                    'sku' => $prod->sku,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'line_total' => $lineTotal,
                    'created_at' => $saleDate,
                    'updated_at' => $saleDate,
                ];
            }

            $discount = (float) $sb['discount_amount'];
            $grandTotal = max(0, $subtotal - $discount);
            // Round up cash paid to nearest 500 or 1000 for realistic change calculation
            $paidAmount = $sb['payment_method'] === 'Cash'
                ? ceil($grandTotal / 500) * 500
                : $grandTotal;
            if ($paidAmount < $grandTotal) {
                $paidAmount = $grandTotal;
            }
            $changeAmount = round($paidAmount - $grandTotal, 2);

            $sale = Sale::create([
                'invoice_no' => $sb['invoice_no'],
                'user_id' => $sb['user']->id,
                'customer_name' => $sb['customer_name'],
                'customer_phone' => $sb['customer_phone'],
                'items_count' => $itemsCount,
                'subtotal' => $subtotal,
                'discount_amount' => $discount,
                'tax_amount' => 0.00,
                'grand_total' => $grandTotal,
                'paid_amount' => $paidAmount,
                'change_amount' => $changeAmount,
                'payment_method' => $sb['payment_method'],
                'status' => 'completed',
            ]);

            $sale->timestamps = false;
            $sale->created_at = $saleDate;
            $sale->updated_at = $saleDate;
            $sale->save();

            foreach ($linesToCreate as $lineData) {
                SaleItem::create(array_merge($lineData, ['sale_id' => $sale->id]));
            }
        }
    }
}
