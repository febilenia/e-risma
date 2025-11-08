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
        Schema::dropIfExists('opsi_skala');
        Schema::dropIfExists('skala_jawaban');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
