<?php

use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\Public\RequestAttachmentController;
use App\Http\Controllers\Public\RequestFormController;
use App\Http\Controllers\Public\RequestReceiptController;
use App\Http\Controllers\Public\RequestTrackingController;
use App\Http\Controllers\Public\ServiceDetailsController;
use App\Http\Controllers\Public\ServiceDirectoryController;
use App\Http\Controllers\Public\ServiceRequestController;
use App\Http\Controllers\Staff\InternalNoteController;
use App\Http\Controllers\Staff\RequestAppointmentController as StaffRequestAppointmentController;
use App\Http\Controllers\Staff\RequestAssignmentController;
use App\Http\Controllers\Staff\RequestAttachmentController as StaffRequestAttachmentController;
use App\Http\Controllers\Staff\RequestController as StaffRequestController;
use App\Http\Controllers\Staff\RequestTransitionController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('welcome');
})->name('home');

Route::get('services', ServiceDirectoryController::class)->name('services.index');
Route::get('services/{service}', ServiceDetailsController::class)->name('services.show');
Route::get('services/{service}/request', RequestFormController::class)->name('requests.create');
Route::post('requests', [ServiceRequestController::class, 'store'])
    ->middleware('throttle:resident-submissions')
    ->name('requests.store');
Route::get('requests/receipt/{serviceRequest}', RequestReceiptController::class)->name('requests.receipt');

Route::get('track', [RequestTrackingController::class, 'index'])->name('tracking.index');
Route::post('track', [RequestTrackingController::class, 'verify'])
    ->middleware('throttle:resident-tracking')
    ->name('tracking.verify');
Route::get('track/{reference}', [RequestTrackingController::class, 'show'])->name('tracking.show');
Route::get('track/{reference}/attachments/{attachment}', RequestAttachmentController::class)
    ->middleware('throttle:resident-downloads')
    ->name('tracking.attachments.show');

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

    Route::prefix('staff')->name('staff.')->group(function () {
        Route::get('requests', [StaffRequestController::class, 'index'])->name('requests.index');
        Route::get('requests/{serviceRequest}', [StaffRequestController::class, 'show'])->name('requests.show');
        Route::patch('requests/{serviceRequest}/assignment', RequestAssignmentController::class)->name('requests.assignment');
        Route::post('requests/{serviceRequest}/transitions', RequestTransitionController::class)->name('requests.transitions');
        Route::post('requests/{serviceRequest}/notes', InternalNoteController::class)->name('requests.notes');
        Route::patch('requests/{serviceRequest}/appointment', StaffRequestAppointmentController::class)->name('requests.appointment');
        Route::get('requests/{serviceRequest}/attachments/{attachment}', StaffRequestAttachmentController::class)
            ->middleware('throttle:60,1')
            ->name('requests.attachments.show');
    });

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
