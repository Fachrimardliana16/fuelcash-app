<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained(); // Add user_id to track who created this
            $table->string('transaction_number')->unique();
            $table->foreignId('vehicles_id')->constrained();
            $table->foreignId('vehicle_type_id')->constrained();
            $table->string('license_plate');
            $table->string('owner');
            $table->date('usage_date');
            $table->foreignId('fuel_id')->constrained();
            $table->foreignId('fuel_type_id')->constrained();
            $table->decimal('amount', 12, 2);
            $table->decimal('volume', 10, 2)->comment('Fuel volume in liters');
            $table->text('usage_description');
            $table->string('fuel_receipt')->nullable();
            $table->string('invoice')->nullable();
            $table->foreignId('balance_id')->constrained();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('transactions');
    }
};
