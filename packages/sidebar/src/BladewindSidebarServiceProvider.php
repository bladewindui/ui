<?php

namespace Mkocansey\Bladewind\Sidebar;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Mkocansey\Bladewind\Sidebar\Components\Sidebar;

class BladewindSidebarServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/bladewind.php', 'bladewind');
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'bladewind');
        Blade::component(Sidebar::class, 'bladewind::sidebar');

        $this->publishes([
            __DIR__.'/../resources/views/components/' => resource_path('views/components/bladewind'),
        ], 'bladewind-components');
    }
}
