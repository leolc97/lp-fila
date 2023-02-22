<?php

namespace App\Forms\Components;

use Closure;
use Filament\Forms\Components\Repeater;


class Batches extends Repeater
{
    protected string $view = 'forms.components.batches';
    protected int $batch = 1;

    public function getBatch(): int
    {
        return $this->batch;
    }

    public function batch(int | Closure $i = 1): static
    {
        $this->batch = $i;

        return $this;
    }
}
