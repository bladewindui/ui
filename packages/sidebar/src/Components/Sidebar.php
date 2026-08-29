<?php

namespace Mkocansey\Bladewind\Sidebar\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Sidebar extends Component
{
    public mixed $sidebarActive;

    public function __construct(
        public ?string $name = null,
        public string $label = 'Sidebar navigation',
        public mixed $active = null,
        public ?string $placement = null,
        public ?string $mobile = null,
        public ?string $mobileSize = null,
        public mixed $collapsible = null,
        public mixed $collapsed = null,
        public mixed $showCollapseControl = null,
        public mixed $closeOnNavigate = null,
        public mixed $persist = null,
        public mixed $persistGroups = null,
        public ?string $storageKey = null,
        public ?string $height = null,
        public mixed $multipleActive = null,
        public string $collapseLabel = 'Collapse navigation',
        public string $expandLabel = 'Expand navigation',
        public string $closeLabel = 'Close navigation',
    ) {
        $this->name ??= defaultBladewindName('bw-sidebar-');
        $this->placement ??= config('bladewind.sidebar.placement', 'left');
        $this->mobile ??= config('bladewind.sidebar.mobile', 'drawer');
        $this->mobileSize ??= config('bladewind.sidebar.mobile_size', 'small');
        $this->collapsible ??= config('bladewind.sidebar.collapsible', false);
        $this->collapsed ??= config('bladewind.sidebar.collapsed', false);
        $this->showCollapseControl ??= config('bladewind.sidebar.show_collapse_control', true);
        $this->closeOnNavigate ??= config('bladewind.sidebar.close_on_navigate', true);
        $this->persist ??= config('bladewind.sidebar.persist', false);
        $this->persistGroups ??= config('bladewind.sidebar.persist_groups', false);
        $this->height ??= config('bladewind.sidebar.height', 'full');
        $this->multipleActive ??= config('bladewind.sidebar.multiple_active', false);
        $this->sidebarActive = $this->active;
    }

    public function render(): View
    {
        return view('bladewind::components.sidebar.index');
    }
}
