<?php

namespace GeneaLabs\NovaPassportManager\Providers;

use GeneaLabs\NovaPassportManager\Http\Middleware\Authorize;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Laravel\Nova\Events\ServingNova;
use Laravel\Nova\Nova;

class Service extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'nova-passport-manager');

        $this->app->booted(function () {
            $this->routes();
        });

        Nova::serving(function (ServingNova $event) {
            //
        });
    }

    /**
     * Register the tool's routes.
     *
     * @return void
     */
    protected function routes()
    {
        if ($this->app->routesAreCached()) {
            return;
        }

        Nova::router()
            ->group(function ($router) {
                $router->get('nova-passport-manager', function (Request $request) {
                    return inertia('NovaPassportManager');
                });
            });

        Route::middleware(['nova', Authorize::class])
                ->prefix('nova-vendor/nova-passport-manager')
                ->group(__DIR__.'/../../routes/api.php');
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
