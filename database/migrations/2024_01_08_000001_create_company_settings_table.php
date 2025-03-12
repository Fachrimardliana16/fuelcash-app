<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('company_settings', function (Blueprint $table) {
            $table->id();
            $table->string('government_name')->nullable();
            $table->string('company_name');
            $table->string('company_logo')->nullable();
            // Detail alamat
            $table->string('street_address')->nullable();
            $table->string('village')->nullable();
            $table->string('district')->nullable();
            $table->string('regency')->nullable();
            $table->string('province')->nullable();
            $table->string('postal_code')->nullable();
            // Informasi kontak
            $table->string('phone_number')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable()->comment('URL without http/https prefix');
            // Sosial media
            $table->string('facebook')->nullable()->comment('URL without http/https prefix');
            $table->string('instagram')->nullable()->comment('URL without http/https prefix');
            $table->string('twitter')->nullable()->comment('URL without http/https prefix');
            $table->string('youtube')->nullable()->comment('URL without http/https prefix');
            $table->string('linkedin')->nullable()->comment('URL without http/https prefix');
            // Informasi tambahan
            $table->text('description')->nullable();
            $table->string('tax_number')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('company_settings');
    }
};
