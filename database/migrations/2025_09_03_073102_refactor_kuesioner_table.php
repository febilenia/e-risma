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
        Schema::table('kuesioner', function (Blueprint $table) {
             if (Schema::hasColumn('kuesioner','skala_jawaban_id')) {
                $table->dropForeign(['skala_jawaban_id']); 
                $table->dropColumn('skala_jawaban_id');
            }
            if (Schema::hasColumn('kuesioner','is_positive')) {
                $table->dropColumn('is_positive');
            }
            if (Schema::hasColumn('kuesioner','bobot_pertanyaan')) {
                $table->dropColumn('bobot_pertanyaan');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kuesioner', function (Blueprint $table) {
            $table->unsignedBigInteger('skala_jawaban_id')->nullable();
             $table->foreign('skala_jawaban_id')->references('id')->on('skala_jawaban');
            $table->boolean('is_positive')->nullable();
            $table->decimal('bobot_pertanyaan',5,2)->default(0);
        });
    }
};
