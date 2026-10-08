<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class CustomAccountUserWidget extends Widget
{
    protected string $view = 'filament.widgets.custom-account-user-widget';
    protected static ?int $sort = -3;
}
