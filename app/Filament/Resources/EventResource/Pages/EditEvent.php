<?php

namespace App\Filament\Resources\EventResource\Pages;

use App\Filament\Resources\EventResource;
use App\Models\Event;
use Closure;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Pages\Actions;
use Filament\Resources\Form;
use Filament\Resources\Pages\EditRecord;
use Filament\Forms;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class EditEvent extends EditRecord implements Forms\Contracts\HasForms
{

    protected static string $resource = EventResource::class;


//    protected function mutateFormDataBeforeFill(array $data): array
//    {
//        $test = 123;
//        $tickets = $this->getRecord()->tickets()->get()->toArray();
//        $batches = $this->getRecord()->tickets()->with('batches')->get()->pluck('batches')->flatten()->toArray();
//
//        $data['title'] = 'test';
//
//        return $data;
//    }

    protected function getActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }


}
