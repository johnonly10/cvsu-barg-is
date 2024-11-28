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
        Schema::create('facilities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', [
                'rooms',
                'building'
            ]);
            $table->string('slug')->unique();
            $table->text('description');
            $table->text('rules_and_regulations'); 
            $table->string('requirements');
            $table->boolean('featured')->default(false);
            $table->boolean('status')->default(true);
            $table->timestamps();
            
            $table->foreignId('created_by')->constrained(
                table: 'users', indexName: 'facilities_users_id'
            )->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('facilities');
    }
};
