<?php

namespace Timuchen\LaravelCommerceml3\Providers;

use Illuminate\Support\ServiceProvider;
use Route;

class Exchange1cProvider extends ServiceProvider
{
    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register()
    {
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishResources();
        $this->registerRoute();
    }

    /**
     * Публикация ресурсов
     */
    private function publishResources()
    {
        // конфигурационный файл
        $this->publishes([
            __DIR__
            .'/../../config/configExchange1C.php' => config_path('configExchange1C.php'),
        ], 'Exchange1cConfig');

        $this->mergeConfigFrom(
            __DIR__.'/../../config/configExchange1C.php',
            'configExchange1C'
        );
    }

    /**
     * Регистрация роута
     */
    private function registerRoute()
    {
        Route::group(
            [
                'namespace'  => 'Timuchen\LaravelCommerceml3\Http\Controllers',
                'middleware' => config('configExchange1C.middleware'),
            ],
            function () {
                Route::match(
                    ['get', 'post'],
                    config('configExchange1C.1cRouteNameCatalog'),
                    'CatalogController@catalogIn'
                )->name('1cExchange');
            }
        );
    }
}
