<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('fuel_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('desc')->nullable();
            $table->boolean('isactive')->default(true)->index();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('fuel_types');
    }
};
