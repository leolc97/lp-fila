<?php

namespace App\Filament\Resources\EventResource\Widgets;
use App\Models\Event;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;

use Filament\Widgets\Widget;

class EventOverview extends Widget
{
    protected static string $view = 'filament.resources.event-resource.widgets.event-overview';

    protected function getCards(): array
    {
        return [
            Card::make('Eventos Ativos', Event::all()->count())
                ->descriptionIcon('heroicon-s-trending-up')
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->color('success'),
        ];
    }
}
