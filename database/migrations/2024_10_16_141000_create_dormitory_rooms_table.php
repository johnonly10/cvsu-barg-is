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
        Schema::create('dormitory_rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rental_id')->constrained('rentals')->onDelete('cascade');
            $table->string('room_number');
            $table->unsignedInteger('room_capacity');
            $table->enum('room_status', ['empty', 'not full', 'full'])->default('empty');
            $table->date('start_date'); 
            $table->date('end_date'); 
            $table->date('ih_start_date')->nullable();
            $table->date('ih_end_date')->nullable();
            $table->enum('dorm_type', ['shared','solo'])->nullable();
            


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dormitory_rooms');
    }
};
