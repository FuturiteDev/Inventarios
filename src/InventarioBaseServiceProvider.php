<?php

namespace Ongoing\Inventarios;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class InventarioBaseServiceProvider extends ServiceProvider {

    public function boot(){
        $this->registerResources();
    }

    public function register(){
        $this->commands([
            Console\DemoCommand::class
        ]);
    }

    private function registerResources(){
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'inventarios');

        $this->registerRoutes();
    }

    protected function registerRoutes(){
        Route::group([], function(){
            $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        });

        Route::group([
            "prefix" => "api",
            "middleware" => ['api']
        ], function(){
            $this->loadRoutesFrom(__DIR__.'/../routes/api.php');
        });
    }
}