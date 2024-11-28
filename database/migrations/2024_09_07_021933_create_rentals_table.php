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
        Schema::create('rentals', function (Blueprint $table) {
            $table->id();
            $table->enum('name', [
                'Male Dormitory',
                'Female Dormitory',
                'International House II',
                'International Convention Center',
                'Rolle Hall',
                'Swimming Pool'
            ]);

            $table->enum('sex', ['male', 'female', 'all'])->default('all');            
            $table->string('slug')->unique();
            $table->text('description');
            $table->string('qualification')->nullable(); 
            $table->decimal('internal_price', 8, 2)->nullable(); 
            $table->decimal('external_price', 8, 2)->nullable(); 
            // $table->decimal('internal_quantity', 8, 2)->nullable(); 
            // $table->decimal('external_quantity', 8, 2)->nullable();
            $table->decimal('pool_quantity', 8, 2)->nullable();
            $table->decimal('exclusive_price', 8, 2)->nullable(); 
            $table->decimal('price', 8, 2)->nullable();
            $table->unsignedInteger('capacity')->nullable();
            $table->enum('status', ['available', 'not available'])->default('available');
            $table->boolean('featured')->default(false);
            $table->string('image');
            $table->text('images');
            

            $table->text('rules_and_regulations'); 
            $table->string('requirements'); 

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rentals');
    }
};
