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
        Schema::table('kategori_kuesioner', function (Blueprint $table) {
            $table->dropColumn('bobot_kategori');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kategori_kuesioner', function (Blueprint $table) {
            $table->decimal('bobot_kategori',5,2)->default(0);
        });
    }
};
