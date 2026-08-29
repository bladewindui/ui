<?php

namespace Mkocansey\Bladewind\DataGrid;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Mkocansey\Bladewind\DataGrid\Components\DataGrid;

class BladewindDataGridServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/bladewind.php', 'bladewind');
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'bladewind');
        Blade::component(DataGrid::class, 'bladewind::data-grid');

        $this->publishes([
            __DIR__.'/../resources/views/components/' => resource_path('views/components/bladewind'),
        ], 'bladewind-components');
    }
}
