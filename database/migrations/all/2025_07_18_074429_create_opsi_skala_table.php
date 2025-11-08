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
        Schema::create('opsi_skala', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->integer('nilai'); // nilai asli
            $table->integer('urutan');
            $table->unsignedBigInteger('skala_jawaban_id')->nullable();

            $table->foreign('skala_jawaban_id')->references('id')->on('skala_jawaban')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('opsi_skala');
    }
};
