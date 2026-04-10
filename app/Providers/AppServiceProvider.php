<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Traits\AddonHelper;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;
use App\CentralLogics\Helpers;

class AppServiceProvider extends ServiceProvider
{
    use AddonHelper;
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
        // HTTPS + raíz canónica: evita mixed content en asset(), imágenes y enlaces detrás de Cloudflare.
        if (! $this->app->runningInConsole() && $this->shouldForceHttpsUrls()) {
            URL::forceScheme('https');
            $root = rtrim((string) config('app.url'), '/');
            if (str_starts_with($root, 'https://')) {
                URL::forceRootUrl($root);
            }
        }

        try {
            // Register Observers
            \App\Models\Store::observe(\App\Observers\StoreObserver::class);

            Config::set('addon_admin_routes', $this->get_addon_admin_routes());
            Config::set('get_payment_publish_status', $this->get_payment_publish_status());
            Paginator::useBootstrap();
            foreach (Helpers::get_view_keys() as $key => $value) {
                view()->share($key, $value);
            }
        } catch (\Exception $e) {

        }

    }

    /**
     * Entornos "live" / producción o petición ya en HTTPS (proxy, Cloudflare, ngrok).
     */
    private function shouldForceHttpsUrls(): bool
    {
        if ($this->app->environment(['production', 'live'])) {
            return true;
        }

        $request = request();

        if ($request->secure()) {
            return true;
        }

        if (strtolower((string) $request->header('X-Forwarded-Proto', '')) === 'https') {
            return true;
        }

        $cfVisitor = $request->header('CF-Visitor');
        if (is_string($cfVisitor) && $cfVisitor !== '') {
            $decoded = json_decode($cfVisitor, true);
            if (is_array($decoded) && ($decoded['scheme'] ?? '') === 'https') {
                return true;
            }
        }

        if (str_contains((string) config('app.url'), 'https://')) {
            return true;
        }

        return str_contains($request->header('Host', ''), 'ngrok');
    }
}
