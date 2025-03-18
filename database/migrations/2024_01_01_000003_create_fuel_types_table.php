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
            $table->decimal('max_deposit', 15, 2)->default(0)->comment('Maksimal saldo yang dapat di depositkan');
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
