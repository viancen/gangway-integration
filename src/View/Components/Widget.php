<?php

namespace Gangway\Laravel\View\Components;

use Gangway\Laravel\Facades\Gangway;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Widget extends Component
{
    public function __construct(public string $token) {}

    public function scriptUrl(): string
    {
        return Gangway::widgetScriptUrl();
    }

    public function render(): View
    {
        return view('gangway::widget');
    }
}
