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
        Schema::create('aplikasi', function (Blueprint $table) {
            $table->id();
            $table->string('nama_aplikasi');
            $table->unsignedBigInteger('opd_id'); // ✅ Ganti nama_opd jadi opd_id
            $table->string('link_survey')->nullable();
            $table->string('id_encrypt')->nullable();
            $table->enum('status', ['open', 'closed'])->default('open'); // opsional
            $table->timestamps();

            // Foreign key relasi ke tabel opd
            $table->foreign('opd_id')->references('id')->on('opd')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aplikasi');
    }
};
