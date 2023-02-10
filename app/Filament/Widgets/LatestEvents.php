<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\EventResource;
use Closure;
use Filament\Tables;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Widgets\Widget;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\HtmlString;

class LatestEvents extends BaseWidget
{

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 2;

    protected function getDefaultTableSortColumn(): ?string
    {
        return 'start_date';
    }

    protected function getTableHeading(): string|Htmlable|Closure|null
    {
        return 'Próximos eventos';
    }

    public function getDefaultTableRecordsPerPageSelectOption(): int
    {
        return 5;
    }

    protected function getTableQuery(): Builder
    {

        return EventResource::getEloquentQuery()
            ->orderBy('start_date', 'desc');
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\ImageColumn::make('banner_image')
                ->size(100)
                ->action(Tables\Actions\ViewAction::make('preview_image')
                    ->modalHeading('Preview image')
                    ->modalContent(fn($record) => new HtmlString("<img src=\"/storage/{$record->banner_image}\" />"))
                )
                ->label('Banner'),
            Tables\Columns\IconColumn::make('status')
                ->boolean()
                ->label('Ativo'),
            Tables\Columns\TextColumn::make('title')
                ->sortable()
                ->searchable()
                ->label('Título'),
//            Tables\Columns\TextColumn::make('description')
//                ->label('Descrição')
//                ->limit(20),
//            Tables\Columns\TextColumn::make('address')
//                ->label('Endereço'),
            Tables\Columns\TextColumn::make('start_date')
                ->dateTime()
                ->label('Data de início'),
            Tables\Columns\TextColumn::make('end_date')
                ->dateTime()
                ->label('Data de término'),
        ];
    }

    public function getTableActions(): array
    {
        return [
            Tables\Actions\ViewAction::make('preview_image')
                ->modalHeading('Preview image')
                ->modalContent(fn($record) => new HtmlString("<img src=\"/storage/{$record->banner_image}\" />"))
        ];
    }

    protected function applyFiltersToTableQuery(Builder $query): Builder
    {
        return $query->where('status', true);
    }

}
