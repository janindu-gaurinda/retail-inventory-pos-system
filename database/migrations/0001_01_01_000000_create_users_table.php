<?php

/**
 * ============================================================================
 * FILE PATH: database/migrations/0001_01_01_000000_create_users_table.php
 * DESCRIPTION: Creates 'users', 'password_reset_tokens', and 'sessions' tables.
 * ============================================================================
 *
 * CONNECTED FILES:
 * - User Model (Backend): app/Models/User.php
 * - Database Seeder (Backend): database/seeders/DatabaseSeeder.php
 * - Auth Controller (Backend): app/Http/Controllers/Auth/LoginController.php
 * ============================================================================
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * PART 1: Run the migrations (Creates database tables).
     * Command: php artisan migrate
     */
    public function up(): void
    {
        // Table 1: 'users' table (stores Super Admin & staff accounts)
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('role')->default('super_admin'); // Role column (e.g., 'super_admin')
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });

        // Table 2: 'password_reset_tokens' table
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        // Table 3: 'sessions' table (stores active login sessions)
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * PART 2: Reverse the migrations (Drops tables on rollback).
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
