<?php

namespace App\Filament\Widgets;

use App\Models\Event;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;

class StatsOverview extends BaseWidget
{
    protected function getCards(): array
    {
        return [
            Card::make('Eventos Ativos', Event::all()->count())
                ->description('32k increase')
                ->descriptionIcon('heroicon-s-trending-up')
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->color('success'),
            Card::make('Ingressos vendidos', '1340')
                ->description('3% decrease')
                ->descriptionIcon('heroicon-s-trending-down')
                ->chart([17, 16, 14, 15, 14, 13, 12])
                ->color('danger'),
            Card::make('Receita', '3543')
                ->description('7% increase')
                ->descriptionIcon('heroicon-s-trending-up')
                ->chart([15, 4, 10, 2, 12, 4, 12])
                ->color('success'),
        ];
    }
}
