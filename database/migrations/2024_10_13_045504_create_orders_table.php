<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('subtotal', 10, 2);
            $table->decimal('total', 10, 2);
            $table->string('name');
            $table->string('phone_number');
            $table->string('year_level')->nullable(); 
            $table->string('department')->nullable(); 
            $table->string('course')->nullable(); 
            $table->string('email');
            $table->date('reservation_date')->nullable();
            $table->string('time_slot')->nullable();
            $table->date('picked_up_date')->nullable();
            $table->date('canceled_date')->nullable();
            $table->enum('status', ['reserved', 'pickedup', 'canceled'])->default('reserved');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
