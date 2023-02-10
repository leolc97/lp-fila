<?php

namespace App\Filament\Resources\EventResource\Pages;

use App\Filament\Resources\EventResource;
use App\Forms\Components\Batches;
use Cheesegrits\FilamentGoogleMaps\Fields\Geocomplete;
use Cheesegrits\FilamentGoogleMaps\Fields\Map;
use Closure;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Wizard;
use Filament\Pages\Actions;
use Filament\Forms;
use Filament\Resources\Pages\Concerns\HasWizard;
use Filament\Resources\Pages\CreateRecord;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Wizard\Step;
use Illuminate\Support\Facades\Hash;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\FileUpload;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Livewire\Component as Livewire;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\ViewField;
use Livewire\Component as LivewireComponent;
use Termwind\Components\Li;


class CreateEvent extends CreateRecord
{
    use CreateRecord\Concerns\HasWizard;

    protected static string $resource = EventResource::class;


    protected function getSteps(): array
    {
        return [
            Step::make('testes')
                ->description('Publicar evento')
                ->schema([
//                    Forms\Components\Repeater::make('batches')
//                        ->schema([
//                            Forms\Components\Repeater::make('tickets')
//                                ->relationship()
//                                ->schema([
//                                    Forms\Components\TextInput::make('title')
//                                        ->required()
//                                        ->placeholder('Nome do ingresso')
//                                        ->label('Título')
//                                ])
//
//                        ])
//                        ->afterStateHydrated(function (Forms\Components\Repeater $component) {
//                            $component->getState();
//                        })
                    Forms\Components\Section::make('test')
                        ->schema([
                            Tabs::make('Heading')
                                ->tabs([
                                    Tabs\Tab::make('Label 1')
                                        ->icon('heroicon-o-ticket')
                                        ->badge('39')
                                        ->hidden(fn(Closure $get) => false)
                                        ->schema([
                                            Forms\Components\TextInput::make('email')
                                                ->required()
                                                ->email()
                                                ->unique()
                                                ->placeholder('Email do organizador')
                                                ->label('Email'),
                                        ]),
                                    Tabs\Tab::make('Label 2')
                                        ->hidden(fn(Closure $get) => intval($get('batch_quantity')) < 2)
                                        ->schema([
                                            Forms\Components\TextInput::make('email')
                                                ->required()
                                                ->email()
                                                ->unique()
                                                ->placeholder('Email do organizador')
                                                ->label('Email'),
                                        ]),
                                    Tabs\Tab::make('Label 3')
                                        ->hidden(fn(Closure $get) => intval($get('batch_quantity')) < 3)
                                        ->schema([
                                            Forms\Components\TextInput::make('email')
                                                ->required()
                                                ->email()
                                                ->unique()
                                                ->placeholder('Email do organizador')
                                                ->label('Email'),
                                        ]),
                                ])
                        ])
                ]),
            Step::make('DETALHES')
                ->description('Título & Descrição')
                ->icon('heroicon-o-shopping-bag')
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
                ->description('Datas & Horários')
                ->schema([
                    Forms\Components\Card::make()
                        ->columns(2)
                        ->schema([
                            Forms\Components\DateTimePicker::make('start_date')
                                ->columnSpan(1)
                                ->required()
                                ->label('Data de início'),
                            Forms\Components\TimePicker::make('HORA DE INÍCIO')
                                ->columnSpan(1)
                                ->required()
                                ->label('Hora de início'),
                            Forms\Components\DateTimePicker::make('end_date')
                                ->columnSpan(1)
                                ->label('Data de término'),
                            Forms\Components\TimePicker::make('HORA DE TÉRMINO')
                                ->columnSpan(1)
                                ->required()
                                ->label('Hora de término'),
                            Placeholder::make('Duração do evento')
                                ->content(new HtmlString('<a href="https://filamentphp.com/docs">filamentphp.com</a>'))
                        ])

                ]),
            Step::make('INGRESSOS')
                ->description('Evento Preços')
                ->registerListeners([
                    'wizard::nextStep' => [
                        function (Component $component): void {
                            $livewire = $component->getLivewire();
                            $batchesQuantity = intval(data_get($livewire, 'data.batch_quantity'));
                            $ticket = data_get($livewire, 'data.tickets');
                            for ($i = 1; $i <= $batchesQuantity; $i++) {
                                data_set($livewire, 'data.tickets' . $i, $ticket);

                            }
                        },
                    ],
                ])
                ->schema([
                    Forms\Components\Section::make('Configurações Gerais')
                        ->schema([
                            Forms\Components\DateTimePicker::make('start_date')
                                ->required()
                                ->columnSpan(1)
                                ->label('Quando os ingressos começaram a serem vendidos'),
                            Forms\Components\DateTimePicker::make('end_date')
                                ->columnSpan(1)
                                ->label('Quando os ingressos deixaram de serem vendidos'),
                            Forms\Components\Select::make('batch_quantity')
                                ->reactive()
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
                        ])
                        ->columns(2),
                    Forms\Components\Section::make('Configurações Dos Ingressos')
                        ->schema([
                            Forms\Components\Repeater::make('tickets')
                                ->relationship()
                                ->columns(3)
                                ->schema([
                                    Forms\Components\TextInput::make('title')
                                        ->columnSpanFull()
                                        ->label('Título')
                                        ->lazy()
                                        ->required()
                                        ->maxLength(255),
                                    Forms\Components\TextInput::make('description')
                                        ->columnSpanFull()
                                        ->label('Descrição')
                                        ->maxLength(255),
                                    Forms\Components\TextInput::make('price')
                                        ->columnSpan(1)
                                        ->label('Preço')
                                        ->required()
                                        ->numeric()
                                        ->rules(['regex:/^\d{1,6}(\.\d{0,2})?$/'])
                                        ->mask(fn(TextInput\Mask $mask) => $mask->money(prefix: 'R$', thousandsSeparator: ',', decimalPlaces: 2, isSigned: false)),
                                    Forms\Components\TextInput::make('quantity')
                                        ->columnSpan(1)
                                        ->label('Quantidade')
                                        ->numeric()
                                        ->rules(['integer', 'min:0'])
                                        ->lazy()
                                        ->mask(fn(TextInput\Mask $mask) => $mask
                                            ->numeric()
                                            ->integer()
                                            ->minValue(1),
                                        ),
                                    Forms\Components\TextInput::make('customer_limit')
                                        ->columnSpan(1)
                                        ->label('Limite de compras por cliente')
                                        ->default(5)
                                        ->rules(['integer', 'min:1'])
                                        ->mask(fn(TextInput\Mask $mask) => $mask
                                            ->numeric()
                                            ->integer()
                                            ->minValue(1),

                                        ),
                                    Radio::make('gender')
                                        ->columnSpanFull()
                                        ->hintIcon('heroicon-o-shopping-bag')
                                        ->hintColor('primary')
                                        ->default('unisex')
                                        ->label('Gênero')
                                        ->options([
                                            'male' => 'Masculino',
                                            'female' => 'Feminino',
                                            'unisex' => 'Unisex'
                                        ]),
                                ])
                                ->collapsible()
                                ->collapsed()
                                ->defaultItems(1)
                                ->createItemButtonLabel('Adicionar ingresso')
                                ->itemLabel(fn(array $state): ?string => $state['title'] ?? null)
                                ->label('Ingressos'),
                        ])
                        ->reactive(),

                ]),
            Step::make('LOTES')
                ->description('Configuração dos Lotes')
                ->schema(
                    $this->lotesSchema(),
                ),
            Step::make('LOCALIZAÇÃO')
                ->description('Local & Endereço')
                ->schema([
                    Forms\Components\Repeater::make('locations')
                        ->columnSpanFull()
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
                                    Geocomplete::make('zip')
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
                                            'zip' => '%z',
                                            'street_name' => '%S',
                                            'street_number' => '%n',
                                        ])
                                        ->clickable(true)
                                        ->draggable(true)


                                ])
                        ])
                ]),
            Step::make('SOCIAL')
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
                ->description('Publicar evento')
                ->schema([
                    Forms\Components\Select::make('status')
                        ->options([
                            'draft' => 'Rascunho',
                            'published' => 'Publicado',
                            'cancelled' => 'Cancelado',
                        ])
                        ->required(),
                    Forms\Components\Toggle::make('featured')
                        ->required(),
                    Forms\Components\TextInput::make('offline_payment_info')
                        ->label('Informações de pagamento offline')
                        ->maxLength(255),
                ]),
        ];
    }

    protected function lotesSchema(): array
    {
        $schema = [];
        $aux = [];
        $schema[] = Forms\Components\Section::make('Configurações dos lotes')
            ->schema([
                Radio::make('batch_config')
                    ->options([
                        'individual' => 'Individual',
                        'general' => 'Geral',
                    ])
                    ->default('general')
                    ->label('Configurações dos lotes'),

            ]);

        for ($i = 1; $i <= 10; $i++) {
            $aux[] = Tabs\tab::make('lote' . $i)
                ->icon('heroicon-o-ticket')
                ->schema([
                    Forms\Components\Repeater::make('tickets' . $i)
                        ->columns(3)
                        ->schema([
                            Forms\Components\TextInput::make('title')
                                ->hidden()
                                ->label('Título')
                                ->lazy()
                                ->required()
                                ->maxLength(255),
                            Forms\Components\TextInput::make('price')
                                ->columnSpan(1)
                                ->label('Preço')
                                ->required()
                                ->numeric()
                                ->rules(['regex:/^\d{1,6}(\.\d{0,2})?$/'])
                                ->mask(fn(TextInput\Mask $mask) => $mask->money(prefix: 'R$', thousandsSeparator: ',', decimalPlaces: 2, isSigned: false)),
                            Forms\Components\TextInput::make('quantity')
                                ->columnSpan(1)
                                ->label('Quantidade')
                                ->hidden()
                                ->numeric()
                                ->rules(['integer', 'min:0']),
                            Select::make('batch_type')
                                ->columnSpan(1)
                                ->options([
                                    'date' => 'Data',
                                    'tickets' => 'Ingressos',
                                ])
                                ->reactive()
                                ->default('date')
                                ->label('Virada'),
                            Forms\Components\DateTimePicker::make('limit_date')
                                ->hidden(fn(Closure $get): bool => $get('batch_type') !== 'date')
                                ->label('Data')
                                ->required(),
                            Forms\Components\TextInput::make('limit_tickets')
                                ->hidden(fn(Closure $get): bool => $get('batch_type') !== 'tickets')
                                ->label('Ingressos')
                                ->rules(['integer', 'min:1'])
                                ->mask(fn(TextInput\Mask $mask) => $mask
                                    ->numeric()
                                    ->integer()
                                    ->minValue(1),

                                ),
                        ])
                        ->itemLabel(fn(array $state): ?string => $state['title'] ?? null)
                        ->collapsible()
                        ->disableItemMovement()
                        ->disableItemDeletion()
                        ->disableItemCreation()
                        ->label('Ingressos'),
                ])
                ->label('Lote ' . $i)
                ->hidden(fn(Closure $get) => intval($get('batch_quantity')) < $i);
        }

        $tab = Tabs::make('Lotes')
            ->columnSpanFull()
            ->tabs($aux);
        $tab->getChildComponents()[0]->hidden(false);
        $schema[] = $tab;
        return $schema;

    }


    public function hasSkippableSteps(): bool
    {
        return true;
    }
}
