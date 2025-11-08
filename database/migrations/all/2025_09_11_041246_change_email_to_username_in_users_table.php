<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1) Rename email -> username
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('email', 'username');
        });

        // 2) Tambah unique index utk username
        Schema::table('users', function (Blueprint $table) {
            $table->unique('username', 'users_username_unique');
        });

        // 3) Tambah password_changed_at
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'password_changed_at')) {
                $table->timestamp('password_changed_at')->nullable()->after('password');
            }
        });

        // 4) Hapus email_verified_at (kalau ada)
        if (Schema::hasColumn('users', 'email_verified_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('email_verified_at');
            });
        }
    }

    public function down(): void
    {
        // 1) Tambah balik email_verified_at (opsional)
        if (!Schema::hasColumn('users', 'email_verified_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->timestamp('email_verified_at')->nullable();
            });
        }

        // 2) Hapus index unik di username
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique('users_username_unique');
        });

        // 3) Hapus password_changed_at
        if (Schema::hasColumn('users', 'password_changed_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('password_changed_at');
            });
        }

        // 4) Rename username -> email
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('username', 'email');
        });
    }
};
