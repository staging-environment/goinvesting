<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AssetController;
use App\Http\Controllers\WatchlistController;

Route::get('/', [AssetController::class, 'index'])->name('home');
Route::get('/asset/{symbol}', [AssetController::class, 'show'])->name('assets.show');
Route::get('/api/asset/{symbol}/chart', [AssetController::class, 'getChart'])->name('assets.chart');
Route::get('/api/search', [AssetController::class, 'search'])->name('assets.search');
Route::get('/api/quotes', [AssetController::class, 'getQuotes'])->name('assets.quotes');

Route::middleware('auth')->group(function () {
    Route::post('/watchlist', [WatchlistController::class, 'store'])->name('watchlist.store');
    Route::delete('/watchlist/{symbol}', [WatchlistController::class, 'destroy'])->name('watchlist.destroy');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
