<?php

namespace App\Livewire\Traits;

/**
 * Reset all components properties
 */
trait ClearsProperties
{
    public function clear(): void
    {
        $this->reset($this->filters());
    }

    protected function filters(): array
    {
        return [];
    }
}
