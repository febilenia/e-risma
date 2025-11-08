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
        Schema::table('responden', function (Blueprint $table) {
            //$table->unsignedBigInteger('aplikasi_id')->after('id');

            // Kalau perlu relasi:
            $table->foreign('aplikasi_id')->references('id')->on('aplikasi')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('responden', function (Blueprint $table) {
            $table->dropForeign(['aplikasi_id']);
            $table->dropColumn('aplikasi_id');
        });
    }
};
