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

        // Auto-create storage directory structure and symlink
        try {
            $dirs = [
                storage_path('app/public'),
                storage_path('app/public/inventory'),
                storage_path('app/public/avatars'),
                storage_path('app/public/deliveries'),
                storage_path('app/public/purchase_orders'),
            ];
            foreach ($dirs as $dir) {
                if (!file_exists($dir)) {
                    @mkdir($dir, 0775, true);
                }
            }
            if (!file_exists(public_path('storage'))) {
                @app('files')->link(storage_path('app/public'), public_path('storage'));
            }
        } catch (\Throwable $e) {}
    }
}
