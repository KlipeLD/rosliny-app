<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plants', function (Blueprint $table) {
            $table->string('plant_type')->default('sensor')->after('description');
        });

        DB::table('plants')->update(['plant_type' => 'sensor']);
    }

    public function down(): void
    {
        Schema::table('plants', function (Blueprint $table) {
            $table->dropColumn('plant_type');
        });
    }
};
