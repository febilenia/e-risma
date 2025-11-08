<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kategori_kuesioner', function (Blueprint $table) {
            $table->float('bobot')->default(0)->after('nama_kategori');
        });
    }

    public function down(): void
    {
        Schema::table('kategori_kuesioner', function (Blueprint $table) {
            $table->dropColumn('bobot');
        });
    }
};
