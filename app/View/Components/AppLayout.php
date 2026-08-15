<?php

namespace App\View\Components;

use App\Services\ApplicationIdentityService;
use Illuminate\View\Component;
use Illuminate\View\View;

class AppLayout extends Component
{
    /** @var array<string, mixed> */
    public array $identity;

    public function __construct(ApplicationIdentityService $identityService)
    {
        $this->identity = $identityService->get();
    }

    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('layouts.app');
    }
}
