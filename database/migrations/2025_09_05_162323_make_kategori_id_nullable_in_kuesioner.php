<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // --- VARIAN PALING SEDERHANA (biasanya cukup) ---
        // Jadikan kolom kategori_id boleh NULL
        DB::statement('ALTER TABLE `kuesioner` MODIFY `kategori_id` BIGINT UNSIGNED NULL');

        // Jika kamu ingin sekaligus memastikan FK ada & aman untuk NULL,
        // kamu bisa pakai blok alternatif di bawah (UNCOMMENT jika perlu):

        /*
        // 1) Drop FK lama (nama default Laravel biasanya ini)
        //    Kalau nama FK kamu beda, sesuaikan ya.
        DB::statement('ALTER TABLE `kuesioner` DROP FOREIGN KEY `kuesioner_kategori_id_foreign`');

        // 2) Ubah kolom jadi NULLABLE
        DB::statement('ALTER TABLE `kuesioner` MODIFY `kategori_id` BIGINT UNSIGNED NULL');

        // 3) Tambah lagi FK dengan ON DELETE SET NULL (opsional tapi recommended)
        DB::statement('ALTER TABLE `kuesioner`
            ADD CONSTRAINT `kuesioner_kategori_id_foreign`
            FOREIGN KEY (`kategori_id`) REFERENCES `kategori_kuesioner`(`id`)
            ON UPDATE CASCADE ON DELETE SET NULL
        ');
        */
    }

    public function down(): void
    {
        // WARNING: ini akan gagal kalau ada data yang kategori_id-nya NULL.
        // Bereskan dulu data NULL (isi dengan id kategori valid) sebelum rollback.
        DB::statement('ALTER TABLE `kuesioner` MODIFY `kategori_id` BIGINT UNSIGNED NOT NULL');

        // Kalau sebelumnya kamu drop+add FK, kamu juga perlu balikkan ke semula.
        // Contoh (UNCOMMENT jika kamu pakai blok alternatif di atas):
        /*
        DB::statement('ALTER TABLE `kuesioner` DROP FOREIGN KEY `kuesioner_kategori_id_foreign`');
        DB::statement('ALTER TABLE `kuesioner`
            ADD CONSTRAINT `kuesioner_kategori_id_foreign`
            FOREIGN KEY (`kategori_id`) REFERENCES `kategori_kuesioner`(`id`)
            ON UPDATE CASCADE
        ');
        */
    }
};
