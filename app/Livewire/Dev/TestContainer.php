<?php

namespace App\Livewire\Dev;

use Livewire\Component;

class TestContainer extends Component
{
    public string $section = 'a';

    public function setSection($section)
    {
        $this->section = $section;
    }

    public function render()
    {
        return view('livewire.dev.test-container');
    }
}
