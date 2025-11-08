<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('kuesioner', function (Blueprint $table) {
            $table->decimal('bobot', 5, 4)->change(); // max 99.9999
        });
    }

    public function down()
    {
        Schema::table('kuesioner', function (Blueprint $table) {
            $table->integer('bobot')->change();
        });
    }
};
