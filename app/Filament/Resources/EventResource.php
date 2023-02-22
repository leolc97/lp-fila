<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EventResource\Pages;
use App\Filament\Resources\EventResource\Widgets\EventOverview;
use App\Forms\Components\Batches;
use App\Models\Event;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Cheesegrits\FilamentGoogleMaps\Fields\Geocomplete;
use Cheesegrits\FilamentGoogleMaps\Fields\Map;
use Closure;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
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


    public static function form(Form $form): Form
    {
        return $form
            ->schema(
                Wizard::make(static::getFormSchema())->skippable()
            );
    }


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


    public static function getFormSchema(): array
    {
        return [
            Step::make('DETALHES')
                ->description('Título & Descrição')
                ->icon('heroicon-o-pencil')
                ->columns(1)
                ->schema([
                    Forms\Components\Card::make()
                        ->schema([
                            Forms\Components\Select::make('user_id')
                                ->relationship('user', 'name')
                                ->searchable()
                                ->required()
                                ->placeholder('Selecione o organizador')
                                ->label('Organizador')
                                ->createOptionForm([
                                    Forms\Components\TextInput::make('name')
                                        ->required()
                                        ->placeholder('Nome do organizador')
                                        ->label('Nome'),

                                    Forms\Components\TextInput::make('email')
                                        ->required()
                                        ->email()
                                        ->unique()
                                        ->placeholder('Email do organizador')
                                        ->label('Email'),

                                    Forms\Components\TextInput::make('phone')
                                        ->placeholder('Telefone do organizador')
                                        ->label('Telefone'),
                                    Forms\Components\TextInput::make('password')
                                        ->required()
                                        ->password()
                                        ->dehydrateStateUsing(fn($state) => Hash::make($state))//hash password
                                        ->dehydrated(fn($state) => filled($state))//hash password
                                        ->hiddenOn('edit')
                                        ->placeholder('Senha do organizador')
                                        ->label('Senha'),
                                ])
                                ->createOptionAction(function (Forms\Components\Actions\Action $action) {
                                    return $action
                                        ->modalHeading('Criar organizador')
                                        ->modalButton('Criar organizador')
                                        ->modalWidth('lg');
                                }),
                            Forms\Components\TextInput::make('title')
                                ->required()
                                ->maxLength(100)
                                ->label('Título')
                                ->placeholder('Digite o título do evento'),
                            Forms\Components\RichEditor::make('description')
                                ->maxLength(500)
                                ->label('Descrição'),
                        ]),
                ]),
            Step::make('HORÁRIOS')
                ->icon('heroicon-o-calendar')
                ->description('Datas & Horários')
                ->schema([
                    Forms\Components\Card::make()
                        ->columns(2)
                        ->schema([
                            Forms\Components\DateTimePicker::make('start_date')
                                ->columnSpan(1)
                                ->withoutSeconds()
                                ->minDate(now())
                                ->reactive()
                                ->required()
                                ->label('Data de início'),

                            Forms\Components\DateTimePicker::make('end_date')
                                ->columnSpan(1)
                                ->withoutSeconds()
                                ->minDate(fn(Closure $get) => $get('start_date'))
                                ->reactive()
                                ->label('Data de término'),

                            Placeholder::make('Duração do evento')
                                ->disableLabel()
                                ->content(function (Closure $get) {
                                    $diff = Carbon::create($get('start_date'))->diffForHumans(Carbon::create($get('end_date')),
                                        [ 'parts' => 3, 'join' => true, 'syntax' => CarbonInterface::DIFF_ABSOLUTE]);
                                    return new HtmlString("<h4><strong>Duração</strong> {$diff}</h4>");
                                })
                        ])

                ]),
            Step::make('INGRESSOS')
                ->icon('heroicon-o-ticket')
                ->description('Lotes & Preços')
                ->schema([
                    Forms\Components\Section::make('Configurações Gerais')
                        ->schema([
                            Forms\Components\DateTimePicker::make('start_date_sale')
                                ->required()
                                ->reactive()
                                ->withoutSeconds()
                                ->minDate(now())
                                ->columnSpan(1)
                                ->label('Quando os ingressos começaram a serem vendidos'),
                            Forms\Components\DateTimePicker::make('end_date_sale')
                                ->withoutSeconds()
                                ->reactive()
                                ->minDate(fn(Closure $get) => $get('start_date_sale'))
                                ->columnSpan(1)
                                ->label('Quando os ingressos deixaram de serem vendidos'),
                            Forms\Components\Select::make('batch_quantity')
                                ->required()
                                ->reactive()
                                ->afterStateUpdated(function ($state, Livewire $livewire) {
                                    data_set($livewire, 'data.tickets.*.batches', []);
                                    foreach (Pages\CreateEvent::getBatchesComponents($livewire) as $batch) {
                                        for ($i = 1; $i <= intval($state); $i++) {
                                            $newUuid = (string)Str::uuid();
                                            data_set($livewire, "{$batch->getStatePath()}.{$newUuid}", []);
                                            $batch->getChildComponentContainers()[$newUuid]->fill(['ticket_batch' => $i]);
                                        }
                                    }
                                })
                                ->options([
                                    '1' => 1,
                                    '2' => 2,
                                    '3' => 3,
                                    '4' => 4,
                                    '5' => 5,
                                    '6' => 6,
                                    '7' => 7,
                                    '8' => 8,
                                    '9' => 9,
                                    '10' => 10,
                                ])
                                ->label('Quantidade de lotes'),
                            Radio::make('batch_turn')
                                ->options([
                                    'ticket' => 'Individual',
                                    'batch' => 'Geral',
                                ])
                                ->default('batch')
                                ->label('Virada de lote'),

                        ])
                        ->columns(2),
                    Forms\Components\Section::make('Configurações Dos Ingressos')
                        ->schema([
                            Forms\Components\Repeater::make('tickets')
                                ->label('Ingressos')
                                ->columns(6)
                                ->minItems(1)
                                ->relationship()
                                ->itemLabel(fn(array $state): ?string => $state['title'] ?? null)
                                ->schema([
                                    Forms\Components\TextInput::make('title')
                                        ->columnSpanFull()
                                        ->label('Título')
                                        ->required()
                                        ->maxLength(255)
                                        ->lazy(),
                                    Forms\Components\TextInput::make('description')
                                        ->columnSpanFull()
                                        ->label('Descrição')
                                        ->placeholder('Detalhes extras')
                                        ->maxLength(255),
                                    Forms\Components\TextInput::make('quantity')
                                        ->columnSpan(3)
                                        ->label('Quantidade')
                                        ->numeric()
                                        ->rules(['integer', 'min:1']),
                                    Forms\Components\TextInput::make('customer_limit')
                                        ->columnSpan(3)
                                        ->label('Limite de compras por cliente')
                                        ->helperText('Limite de ingressos que um cliente pode comprar')
                                        ->default(5)
                                        ->rules(['integer', 'min:1']),
                                    Radio::make('gender')
                                        ->columnSpanFull()
                                        ->inline()
                                        ->default('unisex')
                                        ->label('Gênero')
                                        ->options([
                                            'male' => 'Masculino',
                                            'female' => 'Feminino',
                                            'unisex' => 'Unisex'
                                        ]),
                                    Forms\Components\Repeater::make("batches")
                                        ->columnSpanFull()
                                        ->relationship()
                                        ->defaultItems(fn(Closure $get): ?string => $get('../../batch_quantity') ?? '1')
                                        ->schema([
                                            Placeholder::make('batch_placeholder')
                                                ->content(function (Closure $get) {
                                                    return "Lote {$get('ticket_batch')}";
                                                })
                                                ->disableLabel(),
                                            Hidden::make('ticket_batch'),
                                            Forms\Components\TextInput::make('price')
                                                ->columnSpan(1)
                                                ->label('Preço')
                                                ->required()
                                                ->numeric(),
//                                                ->mask(fn(TextInput\Mask $mask) => $mask->money(prefix: 'R$', thousandsSeparator: ',', decimalPlaces: 2, isSigned: false)),
                                            Select::make('batch_type')
                                                ->required()
                                                ->columnSpan(1)
                                                ->options([
                                                    'date' => 'Data',
                                                    'tickets' => 'Ingressos',
                                                ])
                                                ->hidden(fn(Closure $get, Component $component): bool => array_key_last($get('../../batches')) === $component->getContainer()->getStatePath(false))
                                                ->reactive()
                                                ->label('Virada'),
                                            Forms\Components\DateTimePicker::make('limit_date')
                                                ->hidden(fn(Closure $get): bool => $get('batch_type') !== 'date')
                                                ->label('Data'),
                                            Forms\Components\TextInput::make('limit_tickets')
                                                ->hidden(fn(Closure $get): bool => $get('batch_type') !== 'tickets')
                                                ->label('Ingressos'),
                                        ])
                                        ->afterStateHydrated(function (Closure $get, Closure $set) {
                                            $i = 1;
                                            foreach ($get('batches') as $key => $batch) {
                                                $set("batches.{$key}.ticket_batch", $i);
                                                $i++;
                                            }
                                        })
                                        ->disableItemMovement()
                                        ->disableItemDeletion()
                                        ->disableItemCreation()
                                        ->disableLabel()

                                ])
                        ])
                ]),
            Step::make('LOCAL')
                ->icon('heroicon-o-map')
                ->description('Endereços')
                ->schema([
                    Forms\Components\Repeater::make('locations')
                        ->columnSpanFull()
                        ->label('Localização')
                        ->minItems(1)
                        ->createItemButtonLabel('Adicionar outro local')
                        ->relationship()
                        ->schema([
                            Forms\Components\Card::make()
                                ->schema([
                                    Geocomplete::make('street_name')
                                        ->disabled()
                                        ->label('Nome da Rua')
                                        ->types(['street_address'])
                                        ->placeField('name')
                                        ->placeholder('Digite a rua'),
                                    Geocomplete::make('street_number')
                                        ->disabled()
                                        ->label('Número Da Rua')
                                        ->types(['street_number'])
                                        ->placeField('name')
                                        ->placeholder('Digite o número da rua'),
                                    Geocomplete::make('neighborhood')
                                        ->disabled()
                                        ->label('Bairro')
                                        ->types(['sublocality'])
                                        ->placeField('name')
                                        ->placeholder('Digite o bairro'),
                                    Geocomplete::make('postal_code')
                                        ->disabled()
                                        ->types(['postal_code'])
                                        ->placeField('name')
                                        ->label('CEP')
                                        ->rules(['required', 'regex:/^\d{5}-\d{3}$/'])
                                        ->placeholder('Digite o CEP'),
                                    Geocomplete::make('full_address')
                                        ->label('Endereço Completo')
                                        ->placeholder('Digite o endereço do evento'),
                                    Map::make('location')
                                        ->defaultLocation(['-20.482390', '-54.593466'])
                                        ->height(fn() => '600px')
                                        ->defaultZoom(13)
                                        ->autocomplete('full_address')
                                        ->label('Click ou arraste o marcador para definir a localização do evento')
                                        ->autocompleteReverse(true) // reverse geocode marker location to autocomplete field
                                        ->reverseGeocode([
                                            //                                    'full_address' => '%S, %n - %z',
                                            'postal_code' => '%z',
                                            'street_name' => '%S',
                                            'street_number' => '%n',
                                        ])
                                        ->clickable(true)
                                        ->draggable(true)


                                ])
                        ])
                ]),
            Step::make('SOCIAL')
                ->icon('heroicon-o-photograph')
                ->description('Miniatura & Poster')
                ->schema([
                    Forms\Components\FileUpload::make('banner_image')
                        ->image()
                        ->label('Imagem do banner'),
                    Forms\Components\FileUpload::make('thumbnail_image')
                        ->image()
                        ->label('Imagem da thumbnail'),
                ]),
            Step::make('PUBLICAR')
                ->icon('heroicon-o-cog')
                ->description('Publicar evento')
                ->schema([
                    Forms\Components\Select::make('status')
                        ->options([
                            'draft' => 'Rascunho',
                            'published' => 'Publicado',
                            'cancelled' => 'Cancelado',
                        ])
                        ->required(),
                    Forms\Components\Toggle::make('featured')->label("Evento em destaque?"),
                    Forms\Components\TextInput::make('offline_payment_info')
                        ->label('Instruções para pagamento offline')
                        ->maxLength(255),
                ]),
        ];
    }


}
