<?php

use App\Livewire\Products\Create;
use App\Livewire\Products\Index;
use App\Livewire\Products\Serials\Create as SerialsCreate;
use App\Livewire\Products\Serials\Index as SerialsIndex;
use App\Livewire\Products\Serials\Show as SerialsShow;
use App\Livewire\Products\Serials\Update as SerialsUpdate;
use App\Livewire\Products\Show;
use App\Livewire\Products\Update;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'role:admin'])->group(function () {
    // Productos - CRUD
    Route::livewire('products/create', Create::class)->name('products.create');
    Route::livewire('products/{product:slug}/update', Update::class)->name('products.update');
});

Route::middleware(['auth', 'verified', 'permission:seriales.crear'])->group(function () {
    Route::livewire('products/{product:slug}/serials/create', SerialsCreate::class)->name('products.serials.create');
});

Route::middleware(['auth', 'verified', 'permission:seriales.editar'])->group(function () {
    Route::livewire('products/serials/{serial:id}/update', SerialsUpdate::class)->name('products.serials.update');
});

Route::middleware(['auth', 'verified', 'role:admin|vendedor'])->group(function () {
    // Productos - CRUD
    Route::livewire('products', Index::class)->name('products.index');
    Route::livewire('products/{product:slug}', Show::class)->name('products.show');

    // Product Serials - Gestión de Stock
    Route::livewire('products/{product:slug}/serials', SerialsIndex::class)->name('products.serials.index');
    Route::livewire('products/serials/{serial:id}', SerialsShow::class)->name('products.serials.show');
});
