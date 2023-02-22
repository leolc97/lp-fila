<?php

namespace App\Filament\Resources\EventResource\Pages;

use App\Filament\Resources\EventResource;
use App\Forms\Components\Batches;
use App\Models\Batch;
use App\Models\Event;
use App\Models\Ticket;
use App\Models\User;
use Cheesegrits\FilamentGoogleMaps\Fields\Geocomplete;
use Cheesegrits\FilamentGoogleMaps\Fields\Map;
use Closure;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\Wizard;
use Filament\Pages\Actions;
use Filament\Forms;
use Filament\Pages\Actions\Action;
use Filament\Resources\Pages\Concerns\HasWizard;
use Filament\Resources\Pages\CreateRecord;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Wizard\Step;
use Filament\Support\Exceptions\Halt;
use Illuminate\Support\Arr;
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


class CreateEvent extends CreateRecord implements Forms\Contracts\HasForms
{

    use CreateRecord\Concerns\HasWizard;

    protected static string $resource = EventResource::class;

    public function mount(): void
    {
        $this->form->fill($this->eventFill());
    }

    protected function getSteps(): array
    {
        return EventResource::getFormSchema();

    }


    protected function lotesSchema(): array
    {
        $schema = [];
        $aux = [];
        $schema[] = Forms\Components\Section::make('Configurações dos lotes')
            ->schema([
                Radio::make('batch_turn')
                    ->options([
                        'ticket' => 'Individual',
                        'batch' => 'Geral',
                    ])
                    ->default('batch')
                    ->label('Virada de lote'),

            ]);

        for ($i = 1; $i <= 10; $i++) {
            $aux[] = Tabs\Tab::make($i)
                ->icon('heroicon-o-ticket')
                ->schema([
                    Forms\Components\Repeater::make('batch' . $i)
                        ->columns(3)
                        ->schema([
                            Forms\Components\TextInput::make('title')
                                ->hidden()
                                ->label('Título')
                                ->lazy()
                                ->required()
                                ->maxLength(255),
                            Forms\Components\TextInput::make('batch_number')
                                ->columnSpan(1)
                                ->label('Quantidade')
                                ->hidden()
                                ->default($i)
                                ->numeric()
                                ->rules(['integer', 'min:1']),
                            Forms\Components\TextInput::make('price')
                                ->columnSpan(1)
                                ->label('Preço')
                                ->required()
                                ->numeric()
                                ->mask(fn(TextInput\Mask $mask) => $mask->money(prefix: 'R$', thousandsSeparator: ',', decimalPlaces: 2, isSigned: false)),
                            Select::make('batch_type')
                                ->required()
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
                                ->label('Data'),
                            Forms\Components\TextInput::make('limit_tickets')
                                ->hidden(fn(Closure $get): bool => $get('batch_type') !== 'tickets')
                                ->label('Ingressos')
                                ->mask(fn(TextInput\Mask $mask) => $mask
                                    ->numeric()
                                    ->integer()
                                    ->minValue(1),

                                ),
                        ])
                        ->itemLabel(fn(array $state): ?string => $state['title'] ?? null)
//                        ->visible(function (Component $component) {
//                            $batch_number = data_get($component->getState(), '*.batch_number');
//                            $step_number = $component->getContainer()->getParentComponent()->getId();
//                            return intval($step_number) === $batch_number;
//                        })
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

    protected function batchesSchema(): array
    {
        $schema = [];
        $aux = [];
        $schema[] = Forms\Components\Section::make('Configurações dos lotes')
            ->schema([
                Radio::make('batch_turn')
                    ->options([
                        'ticket' => 'Individual',
                        'batch' => 'Geral',
                    ])
                    ->default('batch')
                    ->label('Virada de lote'),

            ]);

        for ($i = 1; $i <= 10; $i++) {
            $aux[] = Tabs\Tab::make("{$i}")
                ->icon('heroicon-o-ticket')
                ->schema([
                    Forms\Components\Repeater::make('batches')
                        ->columns(3)
//                        ->afterStateHydrated(function (Component $component) use ($i) {
//                            $component->statePath("tickets.*.batch.{$i}");
//                        })
                        ->statePath("batches.{$i}")
                        ->schema([
                            Forms\Components\TextInput::make('title')
                                ->hidden()
                                ->label('Título')
                                ->lazy()
                                ->required()
                                ->maxLength(255),
                            Forms\Components\TextInput::make('ticket_batch')
                                ->columnSpan(1)
                                ->label('Quantidade')
                                ->hidden()
                                ->default($i)
                                ->numeric()
                                ->rules(['integer', 'min:1']),
                            Forms\Components\TextInput::make('price')
                                ->columnSpan(1)
                                ->label('Preço')
                                ->required()
                                ->numeric()
                                ->mask(fn(TextInput\Mask $mask) => $mask->money(prefix: 'R$', thousandsSeparator: ',', decimalPlaces: 2, isSigned: false)),
                            Select::make('batch_type')
                                ->required()
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
                                ->label('Data'),
                            Forms\Components\TextInput::make('limit_tickets')
                                ->hidden(fn(Closure $get): bool => $get('batch_type') !== 'tickets')
                                ->label('Ingressos')
                                ->mask(fn(TextInput\Mask $mask) => $mask
                                    ->numeric()
                                    ->integer()
                                    ->minValue(1),

                                ),
                        ])
                        ->reactive()
                        ->itemLabel(fn(array $state): ?string => $state['title'] ?? null)
//                        ->visible(function (Component $component, array $state) {
//                            $batch_number = data_get($component->getState(), '*.batch_number');
//                            $step_number = $component->getContainer()->getParentComponent()->getId();
//                            return intval($step_number) === $batch_number;
//                        })
                        ->collapsible()
                        ->disableItemMovement()
                        ->disableItemDeletion()
                        ->disableItemCreation()
                        ->defaultItems(0)
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

    protected function testSchema(): array
    {
        $schema = [];
        $aux = [];
        $schema[] = Forms\Components\Section::make('Configurações dos Ingressos')
            ->schema([

            ]);

        for ($i = 1; $i <= 10; $i++) {
            $aux[] = Tabs\Tab::make($i)
                ->icon('heroicon-o-ticket')
                ->schema([
                    Forms\Components\Repeater::make('batch' . $i)
                        ->columns(3)
                        ->schema([
                            Forms\Components\TextInput::make('batch_number')
                                ->columnSpan(1)
                                ->label('Quantidade')
                                ->hidden()
                                ->default($i)
                                ->numeric()
                                ->rules(['integer', 'min:1']),
                            Select::make('batch_type')
                                ->required()
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
                                ->label('Data'),
                            Forms\Components\TextInput::make('limit_tickets')
                                ->hidden(fn(Closure $get): bool => $get('batch_type') !== 'tickets')
                                ->label('Ingressos')
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

    protected function tabsSchema(): array
    {
        $tabs = [];
        for ($i = 1; $i <= 10; $i++) {
            $tabs[] = Tabs\Tab::make("{$i}")
                ->icon('heroicon-o-ticket')
                ->schema([
                    Forms\Components\Repeater::make("batches")
                        ->relationship()
                        ->statePath("batches.{$i}")
                        ->columns(3)
                        ->schema([
                            Placeholder::make('batch_placeholder')
//                                ->content(new HtmlString('<h1>Teste</h1>'))
                                ->default('ok')
                                ->afterStateHydrated(function (Closure $set, Placeholder $component) {
                                    $component->content(new HtmlString('<h1>ok</h1>'));
                                })
                                ->disableLabel(),
                            Forms\Components\TextInput::make('price')
                                ->columnSpan(1)
                                ->label('Preço')
                                ->required()
                                ->numeric()
                                ->mask(fn(TextInput\Mask $mask) => $mask->money(prefix: 'R$', thousandsSeparator: ',', decimalPlaces: 2, isSigned: false)),
                            Select::make('batch_type')
                                ->required()
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
                                ->label('Data'),
                            Forms\Components\TextInput::make('limit_tickets')
                                ->hidden(fn(Closure $get): bool => $get('batch_type') !== 'tickets')
                                ->label('Ingressos')
                                ->mask(fn(TextInput\Mask $mask) => $mask
                                    ->numeric()
                                    ->integer()
                                    ->minValue(1),

                                ),
                        ])
                        ->reactive()
                        ->disableItemMovement()
                        ->disableItemDeletion()
                        ->disableItemCreation()
                        ->disableLabel(),
                ])
                ->label('Lote ' . $i)
                ->hidden(fn(Closure $get) => intval($get('../../batch_quantity')) < $i);
        }
        return $tabs;
    }


//    public function create(bool $another = false): void
//    {
//        $this->authorizeAccess();
//
//        $data = data_get($this, 'data.tickets');
//        $batchesQuantity = intval(data_get($this, 'data.batch_quantity'));
//        $event = Event::create($this->form->getState());
//        $this->form->model($event)->saveRelationships();
//        foreach ($data as $key => $value) {
//            $ticket = $event->tickets()->create($value);
//            for ($i = 1; $i <= $batchesQuantity; $i++) {
//                $ticket->batches()->create(data_get($this, "data.batch{$i}.{$key}"));
//            }
//        }
//
//        $this->getCreatedNotification()?->send();
//
//        $this->redirect($this->getRedirectUrl());
//
//
//    }

//    public function create(bool $another = false): void
//    {
//        $this->authorizeAccess();
//
//        try {
//            $this->callHook('beforeValidate');
//
//            $data = $this->form->getState();
//
//            $this->callHook('afterValidate');
//
//            $data = $this->mutateFormDataBeforeCreate($data);
//
//            $this->callHook('beforeCreate');
//
//            $this->record = $this->handleRecordCreation($data);
//
//            $this->form->model($this->record)->saveRelationships();
//
//
//            $tickets = $this->record->tickets()->createMany($data['tickets']);
//
//            collect($tickets)->each(function ($ticket) {
//                $batches = Arr::where($this->data['batches'], function ($batch) use ($ticket) {
//                    return $ticket->title === $batch['title'];
//                });
//                $ticket->batches()->createMany($batches);
//            });
//
//            $dataTicket = data_get($this, 'data.tickets');
//            $dataBatch = data_get($this, 'data.batches');
//            foreach ($dataTicket as $key => $value) {
//                $ticket = $this->record->tickets()->create($value);
//                $batches = Arr::where($dataBatch, function ($v, $k) use ($value) {
//                    return $v['title'] === $value['title'];
//                });
//                $ticket->batches()->createMany($batches);
//
//            }
//
//            $this->callHook('afterCreate');
//        } catch (Halt $exception) {
//            return;
//        }
//
//        $this->getCreatedNotification()?->send();
//
//        if ($another) {
//            // Ensure that the form record is anonymized so that relationships aren't loaded.
//            $this->form->model($this->record::class);
//            $this->record = null;
//
//            $this->fillForm();
//
//            return;
//        }
//
//        $this->redirect($this->getRedirectUrl());
//    }


//    protected
//    function BeforeCreate(): void // create tickets and batches
//    {
//        $test = 123;
//    }
//
//    protected
//    function afterCreate(): void // create tickets and batches
//    {
//        $tickets = $this->record->tickets()->createMany($this->data['tickets']);
//
//        collect($tickets)->each(function ($ticket) {
//            $batches = Arr::where($this->data['batches'], function ($batch) use ($ticket) {
//                return $ticket->title === $batch['title'];
//            });
//            $ticket->batches()->createMany($batches);
//        });
//    }
    public function hasSkippableSteps(): bool
    {
        return true;
    }

    public static function getBatchesComponents($livewire): array
    {
        return collect($livewire->form->getFlatComponents())->filter(function ($value) {
            if (property_exists($value, 'name')) {
                return $value->getName() === 'batches';
            }
            return false;
        })->toArray();
    }

    protected function eventFill(): array
    {
        return [
            'user_id' => auth()->id(),
            'title' => 'titulo',
            'description' => 'descrição',
            'start_date' => now(),
            'end_date' => now(),
            'start_date_sale' => now(),
            'end_date_sale' => now(),
            'batch_quantity' => 3,
            'batch_turn' => 'batch',
            'status' => 'published',
            'tickets' => [
                (string)Str::uuid() => [
                    'title' => 'Ingresso 1',
                    'description' => 'descrição',
                    'quantity' => 100,
                    'customer_limit' => 5,
                    'gender' => 'unisex',
                    'batches' => [
                        (string)Str::uuid() => [
                            'ticket_batch' => 1,
                            'price' => 10,
                            'batch_type' => 'date',
                            'limit_date' => '2023-02-15',
                            'limit_tickets' => null,
                        ],
                        (string)Str::uuid() => [
                            'ticket_batch' => 2,
                            'price' => 20,
                            'batch_type' => 'tickets',
                            'limit_date' => null,
                            'limit_tickets' => 20,
                        ],
                        (string)Str::uuid() => [
                            'ticket_batch' => 3,
                            'price' => 30,
                        ],
                    ],
                ],
                (string)Str::uuid() => [
                    'title' => 'Ingresso 2',
                    'description' => 'descrição',
                    'quantity' => 200,
                    'customer_limit' => 5,
                    'gender' => 'unisex',
                    'batches' => [
                        (string)Str::uuid() => [
                            'ticket_batch' => 1,
                            'price' => 100,
                            'batch_type' => 'date',
                            'limit_date' => '2023-03-25',
                            'limit_tickets' => null,
                        ],
                        (string)Str::uuid() => [
                            'ticket_batch' => 2,
                            'price' => 200,
                            'batch_type' => 'tickets',
                            'limit_date' => null,
                            'limit_tickets' => 200,
                        ],
                        (string)Str::uuid() => [
                            'ticket_batch' => 3,
                            'price' => 300,
                        ],
                    ],
                ],
            ],
        ];
    }


}
