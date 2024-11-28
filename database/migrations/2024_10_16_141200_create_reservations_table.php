<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); 
            $table->foreignId('rental_id')->constrained('rentals')->onDelete('cascade'); 
            $table->foreignId('dormitory_room_id')->nullable()->constrained('dormitory_rooms')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->json('history')->nullable(); 
            $table->date('reservation_date')->nullable(); 
            // $table->json('reservation_ih2_date')->nullable(); 
            $table->date('canceled_date')->nullable(); 
            $table->string('time_slot')->nullable(); 
            $table->enum('rent_status', ['pending', 'reserved', 'completed', 'canceled'])->default('pending'); 
            $table->enum('payment_status', [
                'pending', 
                'advance/deposit complete', 
                '1st month complete', 
                '2nd month complete', 
                '3rd month complete', 
                '4th month complete', 
                '5th month complete', 
                '6th month complete', 
                'full payment complete', 
                'canceled'
            ])->default('pending'); 
            $table->decimal('total_price', 8, 2); 
            $table->unsignedInteger('internal_quantity')->nullable();
            $table->unsignedInteger('external_quantity')->nullable();
            $table->unsignedInteger('pool_quantity')->nullable();
            $table->enum('usage_type', ['individual_group', 'exclusive_use'])->nullable();
            $table->timestamps(); 
        });                                                     
    }
    
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
    
   
};
