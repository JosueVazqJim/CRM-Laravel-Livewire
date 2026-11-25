<?php

use App\Http\Controllers\Quotes\QuotePdfController;
use App\Livewire\Quotes\Create;
use App\Livewire\Quotes\Index;
use App\Livewire\Quotes\Show;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'role:admin|vendedor'])->group(function () {
    Route::livewire('quotes', Index::class)->name('quotes.index');
    Route::livewire('quotes/{quoteVersion}', Show::class)->name('quotes.show');
    Route::livewire('quotes/create/{lead}', Create::class)->name('quotes.create');

    Route::get('quotes/{quoteVersion}/preview', [QuotePdfController::class, 'preview'])->name('quotes.preview');
    Route::get('quotes/{quoteVersion}/pdf', [QuotePdfController::class, 'download'])->name('quotes.pdf.download');
});
