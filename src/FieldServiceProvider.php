<?php

namespace Pcode\SearchableSelect;

use Illuminate\Support\ServiceProvider;
use Laravel\Nova\Events\ServingNova;
use Laravel\Nova\Nova;
use Pcode\SearchableSelect\Http\Middleware\Authorize;
use Route;

class FieldServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Nova::serving(function (ServingNova $event) {
            Nova::script('searchable-select', __DIR__.'/../dist/js/field.js');
            Nova::style('searchable-select', __DIR__.'/../dist/css/field.css');
        });

        $this->app->booted(function () {
            $this->routes();
        });
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

      /**
     * Register the field's routes.
     *
     * @return void
     */
    protected function routes()
    {
        if ($this->app->routesAreCached()) {
            return;
        }

        Route::middleware(['nova', Authorize::class])
                ->prefix('nova-vendor/pcode-searchable-select')
                ->group(__DIR__.'/../routes/api.php');
    }
}


 
  