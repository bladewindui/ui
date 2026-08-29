<?php

namespace Mkocansey\Bladewind\CommandPalette;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Mkocansey\Bladewind\CommandPalette\Components\CommandPalette;

class BladewindCommandPaletteServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/bladewind.php', 'bladewind');
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'bladewind');
        Blade::component(CommandPalette::class, 'bladewind::command-palette');

        $this->publishes([
            __DIR__.'/../resources/views/components/' => resource_path('views/components/bladewind'),
        ], 'bladewind-components');
    }
}
