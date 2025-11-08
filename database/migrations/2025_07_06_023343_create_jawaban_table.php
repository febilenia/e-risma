<?php
// database/migrations/xxxx_xx_xx_create_jawaban_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('jawaban', function (Blueprint $table) {
            $table->id();
            $table->float('skor_normalisasi')->nullable();
            $table->float('skor_terbobot')->nullable();
            $table->tinyInteger('skor')->nullable(); // untuk radio
            $table->text('isi_teks')->nullable();    // untuk free_text
            $table->foreignId('aplikasi_id')->constrained('aplikasi')->onDelete('cascade');
            $table->foreignId('responden_id')->constrained('responden')->onDelete('cascade');
            $table->foreignId('kuesioner_id')->constrained('kuesioner')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jawaban');
    }
};
