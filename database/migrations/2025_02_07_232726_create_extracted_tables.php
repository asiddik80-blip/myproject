<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('extracted_tables', function (Blueprint $table) {
            $table->id();
            $table->string('nama')->nullable();
            $table->string('konten')->nullable();
            $table->string('isi')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('extracted_tables');
    }
};
