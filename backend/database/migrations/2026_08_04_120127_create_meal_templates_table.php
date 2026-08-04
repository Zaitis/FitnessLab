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
        Schema::create('meal_templates', function (Blueprint $table) {
            $table->id();
            $table->string('meal_time');
            $table->unsignedSmallInteger('calories');
            $table->unsignedSmallInteger('protein_g');
            $table->unsignedSmallInteger('fat_g');
            $table->unsignedSmallInteger('carbs_g');
            $table->jsonb('name');
            $table->jsonb('description');
            $table->timestamps();

            $table->index('meal_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meal_templates');
    }
};
