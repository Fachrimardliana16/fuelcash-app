<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained(); // Add user_id to track who created this
            $table->foreignId('fuel_type_id')->constrained(); // Add fuel_type_id to track different fuel balances
            $table->date('date');
            $table->decimal('deposit_amount', 12, 2);
            $table->decimal('remaining_balance', 12, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('balances');
    }
};
