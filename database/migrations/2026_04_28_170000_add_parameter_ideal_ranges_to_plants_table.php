<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plants', function (Blueprint $table) {
            $table->decimal('temp_ideal_min', 5, 2)->nullable()->after('watering_interval_days');
            $table->decimal('temp_ideal_max', 5, 2)->nullable()->after('temp_ideal_min');
            $table->decimal('ph_ideal_min', 4, 2)->nullable()->after('temp_ideal_max');
            $table->decimal('ph_ideal_max', 4, 2)->nullable()->after('ph_ideal_min');
            $table->decimal('ec_ideal_min', 10, 2)->nullable()->after('ph_ideal_max');
            $table->decimal('ec_ideal_max', 10, 2)->nullable()->after('ec_ideal_min');
            $table->decimal('n_ideal_min', 10, 2)->nullable()->after('ec_ideal_max');
            $table->decimal('n_ideal_max', 10, 2)->nullable()->after('n_ideal_min');
            $table->decimal('p_ideal_min', 10, 2)->nullable()->after('n_ideal_max');
            $table->decimal('p_ideal_max', 10, 2)->nullable()->after('p_ideal_min');
            $table->decimal('k_ideal_min', 10, 2)->nullable()->after('p_ideal_max');
            $table->decimal('k_ideal_max', 10, 2)->nullable()->after('k_ideal_min');
            $table->decimal('salt_ideal_min', 10, 2)->nullable()->after('k_ideal_max');
            $table->decimal('salt_ideal_max', 10, 2)->nullable()->after('salt_ideal_min');
        });
    }

    public function down(): void
    {
        Schema::table('plants', function (Blueprint $table) {
            $table->dropColumn([
                'temp_ideal_min',
                'temp_ideal_max',
                'ph_ideal_min',
                'ph_ideal_max',
                'ec_ideal_min',
                'ec_ideal_max',
                'n_ideal_min',
                'n_ideal_max',
                'p_ideal_min',
                'p_ideal_max',
                'k_ideal_min',
                'k_ideal_max',
                'salt_ideal_min',
                'salt_ideal_max',
            ]);
        });
    }
};
