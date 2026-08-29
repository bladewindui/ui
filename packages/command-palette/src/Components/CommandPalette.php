<?php

namespace Mkocansey\Bladewind\CommandPalette\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class CommandPalette extends Component
{
    public function __construct(
        public ?string $name = null,
        public string $label = 'Command palette',
        public ?string $placeholder = null,
        public ?string $searchLabel = null,
        public ?string $shortcut = null,
        public ?string $size = null,
        public mixed $open = false,
        public mixed $loading = null,
        public string $emptyText = 'No results found.',
        public string $loadingText = 'Loading…',
        public mixed $closeOnSelect = null,
        public mixed $backdropCanClose = null,
        public mixed $escapeCanClose = null,
        public string $closeLabel = 'Close command palette',
    ) {
        $this->name ??= defaultBladewindName('bw-command-palette-');
        $this->placeholder ??= 'Search for a command…';
        $this->searchLabel ??= $this->label;
        $this->shortcut ??= config('bladewind.command_palette.shortcut', 'mod+k');
        $this->size ??= config('bladewind.command_palette.size', 'medium');
        $this->loading ??= false;
        $this->closeOnSelect ??= config('bladewind.command_palette.close_on_select', true);
        $this->backdropCanClose ??= config('bladewind.command_palette.backdrop_can_close', true);
        $this->escapeCanClose ??= config('bladewind.command_palette.escape_can_close', true);
    }

    public function render(): View
    {
        return view('bladewind::components.command-palette.index');
    }
}
