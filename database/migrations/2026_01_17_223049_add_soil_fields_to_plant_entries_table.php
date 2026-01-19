<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('plant_entries', function (Blueprint $table) {
            $table->unsignedBigInteger('ts_ms')->nullable()->after('payload');

            $table->decimal('temp_c', 5, 2)->nullable()->after('ts_ms');
            $table->decimal('moist_pct', 5, 2)->nullable()->after('temp_c');
            $table->unsignedInteger('ec_uscm')->nullable()->after('moist_pct');
            $table->decimal('ph', 4, 2)->nullable()->after('ec_uscm');

            $table->unsignedInteger('n_mgkg')->nullable()->after('ph');
            $table->unsignedInteger('p_mgkg')->nullable()->after('n_mgkg');
            $table->unsignedInteger('k_mgkg')->nullable()->after('p_mgkg');

            $table->unsignedInteger('salt_mgl')->nullable()->after('k_mgkg');
        });
    }

    public function down(): void
    {
        Schema::table('plant_entries', function (Blueprint $table) {
            $table->dropColumn([
                'ts_ms','temp_c','moist_pct','ec_uscm','ph',
                'n_mgkg','p_mgkg','k_mgkg','salt_mgl'
            ]);
        });
    }
};
