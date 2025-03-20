<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('fuels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fuel_type_id')->constrained('fuel_types');
            $table->string('name')->unique();
            $table->decimal('price', 10, 2)->index()->nullable();
            $table->string('unit')->nullable();
            $table->boolean('isactive')->default(true)->index();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('fuels');
    }
};
