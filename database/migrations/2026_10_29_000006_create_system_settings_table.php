<?php

/**
 * ============================================================================
 * FILE PATH: database/migrations/2026_10_29_000006_create_system_settings_table.php
 * DESCRIPTION: Database migration for the 'system_settings' table (Module 12).
 *              Stores global store profile, currency, tax/VAT, receipt header/footer,
 *              and inventory alert configuration.
 * ============================================================================
 *
 * CONNECTED FILES:
 * - Model: app/Models/SystemSetting.php
 * - Controller: app/Http/Controllers/Admin/SettingController.php
 * - UI View: resources/views/admin/settings/index.blade.php
 * - Database Seeder: database/seeders/DatabaseSeeder.php
 * ============================================================================
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * PART 1: Create the 'system_settings' table.
     */
    public function up(): void
    {
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('setting_key', 100)->unique();
            $table->text('setting_value')->nullable();
            $table->string('setting_group', 60)->default('general');
            $table->timestamps();
        });
    }

    /**
     * PART 2: Drop the 'system_settings' table on rollback.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};
