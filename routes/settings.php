<?php

use App\Http\Controllers\Settings\PasswordController;
use App\Http\Controllers\Settings\ProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware('auth')->group(function () {
    Route::redirect('settings', '/settings/profile');

    Route::get('settings/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    // Route::get('settings/billing/info', [ProfileController::class, 'billingInfo'])->name('profile.billing.info');
    // Route::get('settings/billing/address', [ProfileController::class, 'billingAddress'])->name('profile.billing.address');
    // Route::get('settings/shipping/address', [ProfileController::class, 'shippingAddress'])->name('profile.shipping.address');
    // Route::patch('settings/billing/info', [ProfileController::class, 'updateBillingInfo'])->name('profile.billing.info.update');
    // Route::patch('settings/billing/address', [ProfileController::class, 'updateBillingAddress'])->name('profile.billing.address.update');
    // Route::patch('settings/shipping/address', [ProfileController::class, 'updateShippingAddress'])->name('profile.shipping.address.update');
    Route::patch('settings/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('settings/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('settings/password', [PasswordController::class, 'edit'])->name('password.edit');

    Route::put('settings/password', [PasswordController::class, 'update'])
        ->middleware('throttle:6,1')
        ->name('password.update');

    Route::get('settings/appearance', function () {
        return Inertia::render('settings/Appearance');
    })->name('appearance');
});
