<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plants', function (Blueprint $table) {
            $table->decimal('temp_min', 5, 2)->nullable()->after('watering_interval_days');
            $table->decimal('temp_max', 5, 2)->nullable()->after('temp_min');
            $table->decimal('ph_min', 4, 2)->nullable()->after('temp_ideal_max');
            $table->decimal('ph_max', 4, 2)->nullable()->after('ph_min');
            $table->decimal('ec_min', 10, 2)->nullable()->after('ph_ideal_max');
            $table->decimal('ec_max', 10, 2)->nullable()->after('ec_min');
            $table->decimal('n_min', 10, 2)->nullable()->after('ec_ideal_max');
            $table->decimal('n_max', 10, 2)->nullable()->after('n_min');
            $table->decimal('p_min', 10, 2)->nullable()->after('n_ideal_max');
            $table->decimal('p_max', 10, 2)->nullable()->after('p_min');
            $table->decimal('k_min', 10, 2)->nullable()->after('p_ideal_max');
            $table->decimal('k_max', 10, 2)->nullable()->after('k_min');
            $table->decimal('salt_min', 10, 2)->nullable()->after('k_ideal_max');
            $table->decimal('salt_max', 10, 2)->nullable()->after('salt_min');
        });
    }

    public function down(): void
    {
        Schema::table('plants', function (Blueprint $table) {
            $table->dropColumn([
                'temp_min',
                'temp_max',
                'ph_min',
                'ph_max',
                'ec_min',
                'ec_max',
                'n_min',
                'n_max',
                'p_min',
                'p_max',
                'k_min',
                'k_max',
                'salt_min',
                'salt_max',
            ]);
        });
    }
};
