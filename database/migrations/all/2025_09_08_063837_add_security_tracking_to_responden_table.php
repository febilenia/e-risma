<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('responden', function (Blueprint $table) {
            // Tambah kolom untuk security & tracking
            $table->string('ip_address', 45)->nullable()->after('jenis_kelamin');
            $table->text('user_agent')->nullable()->after('ip_address');
            $table->string('session_id')->nullable()->after('user_agent');
            
            // Index untuk performa dan monitoring
            $table->index(['aplikasi_id', 'created_at'], 'idx_app_created');
            $table->index(['ip_address', 'created_at'], 'idx_ip_created');
            $table->index('session_id', 'idx_session');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('responden', function (Blueprint $table) {
            $table->dropIndex('idx_session');
            $table->dropIndex('idx_ip_created');
            $table->dropIndex('idx_app_created');
            
            $table->dropColumn(['ip_address', 'user_agent', 'session_id']);
        });
    }
};