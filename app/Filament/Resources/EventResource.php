<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EventResource\Pages;
use App\Filament\Resources\EventResource\RelationManagers;
use App\Filament\Resources\EventResource\Widgets\EventOverview;
use App\Models\Event;
use Carbon\Carbon;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms;
use Livewire\Component as Livewire;


class EventResource extends Resource
{
    protected static ?string $model = Event::class;
    protected static ?string $navigationIcon = 'heroicon-o-calendar';
    protected static ?string $modelLabel = 'Evento';
    protected static ?string $recordTitleAttribute = 'title'; // global search
    protected static ?string $navigationLabel = 'Eventos';

    protected static function getNavigationBadge(): ?string
    {
        return static::getModel()::count(); //arrumar pra eventos ativos
    }
//    protected static ?int $navigationSort = 2;

//    public static function form(Form $form): Form
//    {
//        return $form
//            ->schema([
//            ]);
//    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Organizador'),
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->label('Título'),
//                Tables\Columns\TextColumn::make('description'),
//                Tables\Columns\TextColumn::make('address'),
                Tables\Columns\TextColumn::make('start_date')
                    ->dateTime('M d, h:i')
                    ->label('Data de Início'),
//                Tables\Columns\TextColumn::make('end_date')
//                    ->dateTime(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'danger' => 'cancelled',
                        'warning' => 'draft',
                        'success' => 'published',
                    ]),
                Tables\Columns\IconColumn::make('featured')
                    ->boolean(),
                Tables\Columns\ImageColumn::make('banner_image'),
//                Tables\Columns\ImageColumn::make('thumbnail_image'),
//                Tables\Columns\TextColumn::make('offline_payment_info'),
//                Tables\Columns\TextColumn::make('deleted_at')
//                    ->dateTime(),
//                Tables\Columns\TextColumn::make('created_at')
//                    ->dateTime(),
//                Tables\Columns\TextColumn::make('updated_at')
//                    ->dateTime(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        '1' => 'Ativo',
                        '0' => 'Inativo',
                    ])
                    ->label('Status'),
                Tables\Filters\Filter::make('start_date')
                    ->form([
                        DatePicker::make('start_date')
                            ->label('Data de início'),
                        DatePicker::make('end_date')
                            ->label('Data de término')])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['start_date'],
                                fn(Builder $query, $date): Builder => $query->whereDate('start_date', '>=', $date),
                            )
                            ->when(
                                $data['end_date'],
                                fn(Builder $query, $date): Builder => $query->whereDate('end_date', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['start_date'] ?? null) {
                            $indicators['start_date'] = 'Eventos de ' . Carbon::parse($data['start_date'])->toFormattedDateString();
                        }
                        if ($data['end_date'] ?? null) {
                            $indicators['end_date'] = 'Evento até ' . Carbon::parse($data['end_date'])->toFormattedDateString();
                        }

                        return $indicators;
                    }),
            ])
            ->actions([
//                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
                Tables\Actions\ForceDeleteBulkAction::make(),
                Tables\Actions\RestoreBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\TicketsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEvents::route('/'),
            'create' => Pages\CreateEvent::route('/create'),
            'view' => Pages\ViewEvent::route('/{record}'),
            'edit' => Pages\EditEvent::route('/{record}/edit'),
        ];
    }

    public static function getWidgets(): array
    {
        return [
            EventOverview::class,
        ];
    }

    public static function getFormSchema(?string $section = null)
    {
        if ($section === 'LOTES') {
            return
                Forms\Components\Repeater::make('tickets')
                    ->relationship()
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Título')
                            ->lazy()
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('description')
                            ->label('Descrição')
                            ->lazy()
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('price')
                            ->label('Preço')
                            ->lazy()
                            ->required()
                            ->numeric()
                            ->rules(['regex:/^\d{1,6}(\.\d{0,2})?$/']),
                        Forms\Components\TextInput::make('quantity')
                            ->label('Quantidade')
                            ->lazy()
                            ->numeric()
                            ->rules(['integer', 'min:0']),
                        Forms\Components\TextInput::make('customer_limit')
                            ->label('Limite de compras por cliente')
                            ->lazy()
                            ->numeric()
                            ->rules(['integer', 'min:0']),
                    ])
                    ->collapsible()
                    ->collapsed()
                    ->defaultItems(1)
                    ->createItemButtonLabel('Adicionar ingresso')
                    ->itemLabel(fn(array $state): ?string => $state['title'] ?? null)
                    ->label('Ingressos');
//                    ->afterStateHydrated(function (Livewire $livewire, Component $component) {
//                        $livewire->dispatchBrowserEvent();
//                    });

        }
        return null;
    }

//    public static function getEloquentQuery(): Builder
//    {
//        return parent::getEloquentQuery()
//            ->withoutGlobalScopes([
//                SoftDeletingScope::class,
//            ]);
//    }
}
