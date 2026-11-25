<?php

use App\Livewire\Dash;
use App\Livewire\Dev\TestContainer;
use Illuminate\Support\Facades\Route;

Route::get('/dev/livewire-test', TestContainer::class);

Route::view('/', 'livewire.auth.login')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    //    Route::view('dashboard', 'dashboard')->name('dashboard');
    Route::livewire('dashboard', Dash::class)->name('dashboard');
});

// Route::view('/prueba', 'prueba')->name('prueba');

require __DIR__.'/settings.php';
require __DIR__.'/users.php';
require __DIR__.'/companies.php';
require __DIR__.'/leads.php';
require __DIR__.'/products.php';
require __DIR__.'/notifications.php';
require __DIR__.'/quotes.php';
