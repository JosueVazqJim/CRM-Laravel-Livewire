<?php

use App\Livewire\Users\Create;
use App\Livewire\Users\Index;
use App\Livewire\Users\Show;
use App\Livewire\Users\Update;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::livewire('users', Index::class)->name('users.index');
    Route::livewire('users/create', Create::class)->name('users.create');
    Route::livewire('users/{user:slug}', Show::class)->name('users.show');
    Route::livewire('users/{user:slug}/update', Update::class)->name('users.update');
});

Route::middleware(['auth', 'verified'])->group(function () {});
