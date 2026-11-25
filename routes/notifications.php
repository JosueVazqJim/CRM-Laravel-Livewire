<?php

use App\Livewire\Notifications\Index;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::livewire('notifications', Index::class)->name('notifications.index');
});

Route::middleware(['auth', 'verified'])->group(function () {});
