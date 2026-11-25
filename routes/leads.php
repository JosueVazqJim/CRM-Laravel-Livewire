<?php

use App\Livewire\Leads\Create;
use App\Livewire\Leads\Index;
use App\Livewire\Leads\Merge;
use App\Livewire\Leads\MergeSelect;
use App\Livewire\Leads\Show;
use Illuminate\Support\Facades\Route;

// Rutas accesibles para usuarios autenticados (admin y vendedor)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::livewire('leads', Index::class)->name('leads.index');
    Route::livewire('leads/create', Create::class)->name('leads.create');
    Route::livewire('leads/fusionar', Merge::class)->name('leads.merge');
    Route::livewire('leads/{lead}/fusionar', MergeSelect::class)->name('leads.merge.select');
    Route::livewire('leads/{lead}', Show::class)->name('leads.show');
    // Route::livewire('leads/{lead}/update', Update::class)->name('leads.update');
});
