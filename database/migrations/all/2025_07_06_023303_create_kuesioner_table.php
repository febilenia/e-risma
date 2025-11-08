<?php
// database/migrations/xxxx_xx_xx_create_kuesioner_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('kuesioner', function (Blueprint $table) {
            $table->id();
            $table->text('pertanyaan');
            $table->enum('tipe', ['radio', 'free_text']);
            $table->boolean('is_positive')->nullable();
            $table->unsignedBigInteger('skala_jawaban_id')->nullable();
            $table->boolean('is_mandatory')->default(true);
            $table->integer('urutan')->default(1);
            $table->string('kategori');
            $table->foreign('skala_jawaban_id')->references('id')->on('skala_jawaban')->onDelete('set null');
            $table->unsignedBigInteger('kategori_id')->nullable();
            $table->foreign('kategori_id')->references('id')->on('kategori_kuesioner')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kuesioner');
    }
};
