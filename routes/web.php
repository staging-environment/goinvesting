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

use App\Http\Controllers\TradingController;

Route::middleware('auth')->group(function () {
    Route::post('/watchlist', [WatchlistController::class, 'store'])->name('watchlist.store');
    Route::delete('/watchlist/{symbol}', [WatchlistController::class, 'destroy'])->name('watchlist.destroy');
    
    // Trading Routes
    Route::get('/portfolio', [TradingController::class, 'portfolio'])->name('portfolio');
    Route::post('/api/trade', [TradingController::class, 'executeOrder'])->name('trade.execute');
    Route::post('/portfolio/run-bot', [TradingController::class, 'runBot'])->name('portfolio.run-bot');
    Route::post('/portfolio/complete-wizard', [TradingController::class, 'completeWizard'])->name('portfolio.complete-wizard');
    
    // Admin Routes
    Route::get('/admin/dashboard', [\App\Http\Controllers\AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::post('/admin/user/{id}/update-role', [\App\Http\Controllers\AdminController::class, 'updateRole'])->name('admin.update-role');
    Route::post('/admin/user/{id}/update-limits', [\App\Http\Controllers\AdminController::class, 'updateLimits'])->name('admin.update-limits');
    Route::get('/admin/user/create', [\App\Http\Controllers\AdminController::class, 'createUserForm'])->name('admin.user.create-form');
    Route::post('/admin/user/create', [\App\Http\Controllers\AdminController::class, 'createUser'])->name('admin.user.create');
    Route::get('/admin/user/{id}/edit', [\App\Http\Controllers\AdminController::class, 'editUserForm'])->name('admin.user.edit-form');
    Route::post('/admin/user/{id}/update', [\App\Http\Controllers\AdminController::class, 'updateUser'])->name('admin.user.update');


    // Profile Alpaca Config Route
    Route::post('/profile/alpaca', [ProfileController::class, 'updateAlpaca'])->name('profile.update-alpaca');
    Route::post('/profile/bot-strategy', [ProfileController::class, 'updateBotStrategy'])->name('profile.update-bot-strategy');
});


Route::get('/dashboard', function () {
    return redirect()->route('portfolio');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
