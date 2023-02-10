<?php

namespace App\Filament\Widgets;

use App\Models\Event;
use Filament\Widgets\LineChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class EventsChart extends LineChartWidget
{
    //protected static string $view = 'filament.widgets.events-chart';

    protected static ?int $sort = 2;

    protected function getHeading(): ?string
    {
        return 'Eventos neste mês';
    }

    protected function getData(): array
    {
        $data = Trend::model(Event::class)
            ->between(
                start: now()->startOfMonth(),
                end: now()->endOfMonth(),
            )
            ->perDay()
            ->count();

        return [
            'datasets' => [
                [
                    'label' => 'Eventos',
                    'data' => $data->map(fn(TrendValue $value) => $value->aggregate),
                ],
            ],
            'labels' => $data->map(fn(TrendValue $value) => $value->date),
        ];

    }
}
