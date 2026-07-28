<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Paginator::useBootstrap();
        
        if (config('app.env') === 'production' || request()->server->has('HTTP_X_FORWARDED_PROTO')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }
    }
}
