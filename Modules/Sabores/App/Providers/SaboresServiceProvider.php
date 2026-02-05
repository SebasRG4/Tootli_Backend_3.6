<?php

namespace Modules\Sabores\App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class SaboresServiceProvider extends ServiceProvider
{
    protected $moduleName = 'Sabores';
    protected $moduleNameLower = 'sabores';

    public function boot()
    {
        $this->registerViews();
        $this->registerRoutes();
        $this->loadMigrationsFrom(module_path($this->moduleName, 'database/migrations'));
    }

    public function register()
    {
        $this->app->register(RouteServiceProvider::class);

        // Register custom middleware
        $this->app['router']->aliasMiddleware('image.cache', \Modules\Sabores\App\Http\Middleware\ImageCacheMiddleware::class);
    }

    protected function registerRoutes()
    {
        Route::group([
            'prefix' => 'admin/sabores',
            'as' => 'admin.sabores.',
            'middleware' => ['web', 'admin'],
            'namespace' => 'Modules\Sabores\App\Http\Controllers\Admin',
        ], function ($router) {
            $this->loadRoutesFrom(module_path($this->moduleName, 'routes/web.php'));
        });

        Route::group([
            'prefix' => 'api/v1/sabores',
            'as' => 'api.v1.sabores.',
            'middleware' => ['api'],
            'namespace' => 'Modules\Sabores\App\Http\Controllers\Api',
        ], function ($router) {
            $this->loadRoutesFrom(module_path($this->moduleName, 'routes/api.php'));
        });
    }

    protected function registerViews()
    {
        $viewPath = resource_path('views/modules/' . $this->moduleNameLower);
        $sourcePath = module_path($this->moduleName, 'resources/views');

        $this->publishes([
            $sourcePath => $viewPath
        ], ['views', $this->moduleNameLower . '-module-views']);

        $this->loadViewsFrom(array_merge($this->getPublishableViewPaths(), [$sourcePath]), $this->moduleNameLower);
    }

    private function getPublishableViewPaths(): array
    {
        $paths = [];
        foreach (\Config::get('view.paths') as $path) {
            if (is_dir($path . '/modules/' . $this->moduleNameLower)) {
                $paths[] = $path . '/modules/' . $this->moduleNameLower;
            }
        }
        return $paths;
    }
}
