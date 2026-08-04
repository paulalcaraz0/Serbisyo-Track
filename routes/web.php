<?php

use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\Public\ServiceDetailsController;
use App\Http\Controllers\Public\ServiceDirectoryController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('welcome');
})->name('home');

Route::get('services', ServiceDirectoryController::class)->name('services.index');
Route::get('services/{service}', ServiceDetailsController::class)->name('services.show');

Route::get('privacy', fn () => Inertia::render('public/info', ['pageKey' => 'privacy']))
    ->name('privacy');
Route::get('accessibility', fn () => Inertia::render('public/info', ['pageKey' => 'accessibility']))
    ->name('accessibility');
Route::get('help', fn () => Inertia::render('public/info', ['pageKey' => 'help']))
    ->name('help');

Route::post('locale', LocaleController::class)
    ->middleware('throttle:30,1')
    ->name('locale.update');

Route::middleware(['auth', 'active', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('dashboard');
    })->name('dashboard');

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::resource('services', ServiceController::class)->except(['show', 'destroy']);
        Route::patch('services/{service}/archive', [ServiceController::class, 'archive'])
            ->name('services.archive');
        Route::patch('services/{service}/restore', [ServiceController::class, 'restore'])
            ->name('services.restore');
    });
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
