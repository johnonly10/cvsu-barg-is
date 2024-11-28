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
        Schema::create('week_names', function (Blueprint $table) {
            $table->id(); // Auto-incrementing ID
            $table->integer('week_number')->unique(); // Unique week number
            $table->string('name'); // Week name (e.g., "Week 1", "Week 2", etc.)
            $table->timestamps(); // Created at and updated at timestamps
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('week_names'); // Drop the table on rollback
    }
};
