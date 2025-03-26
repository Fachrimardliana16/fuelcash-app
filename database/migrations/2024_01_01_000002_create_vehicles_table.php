<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('license_plate')->unique();
            $table->string('owner');
            $table->foreignId('vehicle_type_id')->constrained();
            $table->unsignedBigInteger('fuel_type_id')->nullable();
            $table->enum('vehicle_model', ['Pickup', 'Bebek', 'Matic', 'SUV', 'MPV', 'Sport'])->nullable();
            $table->enum('brand', ['Honda', 'Toyota', 'Nissan', 'Suzuki', 'Yamaha', 'Kawasaki', 'Mitsubishi', 'Daihatsu', 'Other'])->nullable();
            $table->string('detail')->nullable();
            $table->enum('ownership_type', ['Inventaris', 'Pribadi'])->default('Inventaris');
            $table->boolean('isactive')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('vehicles');
    }
};
