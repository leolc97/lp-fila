<?php

namespace App\Forms\Components;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Component;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;
use function Filament\Forms\array_move_after;
use function Filament\Forms\array_move_before;


class Batches extends Repeater
{

    protected string $view = 'forms.components.batches';

    protected function setUp(): void
    {
        parent::setUp();

        $this->defaultItems(1);

        $this->afterStateHydrated(static function (Repeater $component, ?array $state): void {
            $items = [];

            foreach ($state ?? [] as $itemData) {
                $items[(string)Str::uuid()] = $itemData;
            }

            $component->state($items);
        });

        $this->registerListeners([
            'repeater::createItem' => [
                function (Repeater $component, string $statePath): void {
                    $livewire = $component->getLivewire();
                    if ($statePath === 'data.tickets_config') {
                        $batches = data_get($livewire, 'data.batches');
                        $tickets = data_get($livewire, 'data.tickets_config');

                        foreach ($batches as $key => $batch) {
                            $batches[$key]['tickets'] = $tickets;
                        }
                        data_set($livewire, $component->getStatePath(), $batches);

                    }
                    if ($statePath === $component->getStatePath()) {
                        $tickets = data_get($livewire, 'data.tickets_config');
                        $newUuid = (string)Str::uuid();

                        data_set($livewire, "{$statePath}.{$newUuid}", []);

                        $component->getChildComponentContainers()[$newUuid]->fill([
                            'tickets' => $tickets,
                        ]);

                        $component->collapsed(false, shouldMakeComponentCollapsible: false);
                    }


                },
            ],
            'repeater::deleteItem' => [
                function (Repeater $component, string $statePath, string $uuidToDelete): void {
                    if ($statePath !== $component->getStatePath()) {
                        return;
                    }

                    $items = $component->getState();

                    unset($items[$uuidToDelete]);

                    $livewire = $component->getLivewire();
                    data_set($livewire, $statePath, $items);
                },
            ],
            'repeater::cloneItem' => [
                function (Repeater $component, string $statePath, string $uuidToDuplicate): void {
                    if ($statePath !== $component->getStatePath()) {
                        return;
                    }

                    $newUuid = (string)Str::uuid();

                    $livewire = $component->getLivewire();
                    data_set(
                        $livewire,
                        "{$statePath}.{$newUuid}",
                        data_get($livewire, "{$statePath}.{$uuidToDuplicate}"),
                    );

                    $component->collapsed(false, shouldMakeComponentCollapsible: false);
                },
            ],
            'repeater::moveItemDown' => [
                function (Repeater $component, string $statePath, string $uuidToMoveDown): void {
                    if ($component->isItemMovementDisabled()) {
                        return;
                    }

                    if ($statePath !== $component->getStatePath()) {
                        return;
                    }

                    $items = array_move_after($component->getState(), $uuidToMoveDown);

                    $livewire = $component->getLivewire();
                    data_set($livewire, $statePath, $items);
                },
            ],
            'repeater::moveItemUp' => [
                function (Repeater $component, string $statePath, string $uuidToMoveUp): void {
                    if ($component->isItemMovementDisabled()) {
                        return;
                    }

                    if ($statePath !== $component->getStatePath()) {
                        return;
                    }

                    $items = array_move_before($component->getState(), $uuidToMoveUp);

                    $livewire = $component->getLivewire();
                    data_set($livewire, $statePath, $items);
                },
            ],
            'repeater::moveItems' => [
                function (Repeater $component, string $statePath, array $uuids): void {
                    if ($component->isItemMovementDisabled()) {
                        return;
                    }

                    if ($statePath !== $component->getStatePath()) {
                        return;
                    }

                    $items = array_merge(array_flip($uuids), $component->getState());

                    $livewire = $component->getLivewire();
                    data_set($livewire, $statePath, $items);
                },
            ],
        ]);

        $this->createItemButtonLabel(static function (Repeater $component) {
            return __('forms::components.repeater.buttons.create_item.label', [
                'label' => lcfirst($component->getLabel()),
            ]);
        });

        $this->mutateDehydratedStateUsing(static function (?array $state): array {
            return array_values($state ?? []);
        });
    }
}
