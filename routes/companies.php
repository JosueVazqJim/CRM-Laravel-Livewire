<?php

use App\Livewire\Companies\Create;
use App\Livewire\Companies\Index;
use App\Livewire\Companies\Show;
use App\Livewire\Companies\Update;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::livewire('companies', Index::class)->name('companies.index');
    Route::livewire('companies/create', Create::class)->name('companies.create');
    Route::livewire('companies/{company:slug}', Show::class)->name('companies.show');
    Route::livewire('companies/{company:slug}/update', Update::class)->name('companies.update');
});

Route::middleware(['auth', 'verified'])->group(function () {});
