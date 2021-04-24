<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        /**
         * App Binding Containers
         */
        $classes = [
            'Product'
            ];

        foreach ($classes as $class) {
            $this->app->bind(
                "App\Contracts\\${class}Repository",
                "App\Repositories\\${class}Repository"
            );
        }

        $this->app->bind(Client::class, function ($app, $parameters) {
            return new Client($parameters[0]);
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
