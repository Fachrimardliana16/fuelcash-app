<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('signatures', function (Blueprint $table) {
            $table->id();
            $table->string('position');  // e.g. 'Kasubag Umum', 'Bendahara BBM'
            $table->string('title');     // e.g. 'Diperiksa Oleh', 'Menyetujui'
            $table->string('name');
            $table->string('nip')->nullable();
            $table->integer('order')->default(0);
            $table->boolean('show_stamp')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('signatures');
    }
};
