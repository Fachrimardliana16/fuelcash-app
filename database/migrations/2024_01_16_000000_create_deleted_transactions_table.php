<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('deleted_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('original_id');
            $table->string('transaction_number')->nullable();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('vehicles_id')->constrained();
            $table->foreignId('vehicle_type_id')->constrained();
            $table->string('license_plate');
            $table->string('owner');
            $table->date('usage_date');
            $table->foreignId('fuel_id')->constrained();
            $table->foreignId('fuel_type_id')->constrained();
            $table->decimal('amount', 12, 2);
            $table->decimal('volume', 8, 2);
            $table->text('usage_description');
            $table->string('fuel_receipt')->nullable();
            $table->string('invoice')->nullable();
            $table->foreignId('balance_id')->constrained();
            $table->foreignId('deleted_by')->constrained('users');
            $table->text('deletion_reason')->nullable();
            $table->timestamp('deleted_at');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('deleted_transactions');
    }
};
