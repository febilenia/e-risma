<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('skala_jawaban', function (Blueprint $table) {
            $table->id();
            $table->string('nama_skala'); // Contoh: "Sangat Tidak Setuju – Sangat Setuju"
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('skala_jawabans');
    }
};
