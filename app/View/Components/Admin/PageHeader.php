<?php

namespace App\View\Components\Admin;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class PageHeader extends Component
{
    public function __construct(
        public string $icon = 'fa-file',
        public string $iconGradient = 'primary',
        public string $eyebrow = '',
        public string $title = '',
        public string $description = '',
    ) {}

    public function render(): View|Closure|string
    {
        return view('components.admin.page-header');
    }
}
