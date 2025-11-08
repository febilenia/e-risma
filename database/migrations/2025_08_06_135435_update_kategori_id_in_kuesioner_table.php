<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateKategoriIdInKuesionerTable extends Migration
{
    public function up(): void
    {
        Schema::table('kuesioner', function (Blueprint $table) {
            // Jika sebelumnya kolom 'kategori' masih ada, hapus dulu
            if (Schema::hasColumn('kuesioner', 'kategori')) {
                $table->dropColumn('kategori');
            }

            // Tambahkan kategori_id dan foreign key-nya
            $table->unsignedBigInteger('kategori_id')->after('bobot');

            $table->foreign('kategori_id')
                ->references('id')
                ->on('kategori_kuesioner')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('kuesioner', function (Blueprint $table) {
            $table->dropForeign(['kategori_id']);
            $table->dropColumn('kategori_id');

            // Bisa juga dikembalikan ke 'kategori' lama jika mau
            $table->string('kategori')->nullable();
        });
    }
}
