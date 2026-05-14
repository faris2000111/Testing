<?php

namespace App\View\Components\Admin;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class EmptyState extends Component
{
    public function __construct(
        public string $icon = 'fa-inbox',
        public string $title = 'Tidak ada data',
        public string $description = '',
    ) {}

    public function render(): View|Closure|string
    {
        return view('components.admin.empty-state');
    }
}
