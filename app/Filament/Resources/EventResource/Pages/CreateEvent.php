<?php

namespace App\Filament\Resources\EventResource\Pages;

use App\Filament\Resources\EventResource;
use Filament\Forms;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

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
            'start_date' => '2023-02-24 07:52:52',
            'end_date' => '2023-02-27 08:56:32',
            'start_date_sale' => '2023-02-10 06:00:00',
            'end_date_sale' => '2023-02-24 12:30:00',
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
