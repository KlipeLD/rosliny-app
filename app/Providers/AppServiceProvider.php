<?php

namespace App\Providers;

use App\Services\Sensors\HttpSensorClient;
use App\Services\Sensors\SensorClient;
use Carbon\CarbonImmutable;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(SensorClient::class, HttpSensorClient::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
    }

    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);
        Paginator::useBootstrapFive();

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

    }
}
