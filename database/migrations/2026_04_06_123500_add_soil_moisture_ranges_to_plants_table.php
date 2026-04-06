<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plants', function (Blueprint $table) {
            $table->decimal('soil_moisture_min', 5, 2)->nullable()->after('photo_path');
            $table->decimal('soil_moisture_max', 5, 2)->nullable()->after('soil_moisture_min');
            $table->decimal('soil_moisture_ideal_min', 5, 2)->nullable()->after('soil_moisture_max');
            $table->decimal('soil_moisture_ideal_max', 5, 2)->nullable()->after('soil_moisture_ideal_min');
        });
    }

    public function down(): void
    {
        Schema::table('plants', function (Blueprint $table) {
            $table->dropColumn([
                'soil_moisture_min',
                'soil_moisture_max',
                'soil_moisture_ideal_min',
                'soil_moisture_ideal_max',
            ]);
        });
    }
};
