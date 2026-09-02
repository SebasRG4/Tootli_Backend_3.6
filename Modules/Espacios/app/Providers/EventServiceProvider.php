<?php

namespace Modules\Espacios\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event handler mappings for the application.
     *
     * @var array<string, array<int, string>>
     */
    protected $listen = [];

    /**
     * Indicates if events should be autodiscovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
