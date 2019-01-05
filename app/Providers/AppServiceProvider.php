<?php

namespace App\Providers;

use App\Services\InfusionsoftClient;
use App\Services\InfusionsoftClientInterface;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(InfusionsoftClientInterface::class, function ($app) {
            return new InfusionsoftClient();
        });
    }
}
