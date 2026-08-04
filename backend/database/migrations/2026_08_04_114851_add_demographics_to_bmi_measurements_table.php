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
        Schema::table('bmi_measurements', function (Blueprint $table) {
            $table->unsignedTinyInteger('age')->nullable()->after('height_cm');
            $table->string('sex')->nullable()->after('age');
            $table->string('activity_level')->nullable()->after('sex');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bmi_measurements', function (Blueprint $table) {
            $table->dropColumn(['age', 'sex', 'activity_level']);
        });
    }
};
