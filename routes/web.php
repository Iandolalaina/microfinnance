<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\MemberRegistrationController;
use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\PushSubscriptionController;
use App\Livewire\AdminDashboard;
use App\Livewire\AgentDashboard;
use App\Livewire\AnnouncementForm;
use App\Livewire\ClientDashboard;
use App\Livewire\ManualPayment;
use App\Livewire\PaymentForm;
use App\Livewire\UserManagement;
use Illuminate\Support\Facades\Route;

Route::view('/', 'home')->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/devenir-membre', [MemberRegistrationController::class, 'showRegistrationForm'])->name('register');
    Route::post('/devenir-membre', [MemberRegistrationController::class, 'register']);
});

Route::middleware('auth')->group(function () {

    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::post('/push-subscriptions', [PushSubscriptionController::class, 'store'])->name('push-subscriptions.store');
    Route::delete('/push-subscriptions', [PushSubscriptionController::class, 'destroy'])->name('push-subscriptions.destroy');

    // ------------------------------------------------------------
    // ESPACE ADMIN
    // ------------------------------------------------------------
    Route::middleware('role:admin')->group(function () {
        Route::get('/admin/dashboard', AdminDashboard::class);
        Route::get('/admin/users', UserManagement::class);
        Route::get('/admin/announcements', AnnouncementForm::class);
    });

    // ------------------------------------------------------------
    // ESPACE AGENT
    // ------------------------------------------------------------
    Route::middleware('role:agent')->group(function () {
        Route::get('/agent/dashboard', AgentDashboard::class);
        Route::get('/agent/manual-payment/{schedule}', ManualPayment::class);
        Route::get('/agent/announcements', AnnouncementForm::class);
        Route::get('/agent/receipts/{payment}', [ReceiptController::class, 'download']);
    });

    // ------------------------------------------------------------
    // ESPACE CLIENT
    // ------------------------------------------------------------
    Route::middleware('role:client')->group(function () {
        Route::get('/client/dashboard', ClientDashboard::class);
        Route::get('/client/payment/{schedule}', PaymentForm::class);
        Route::get('/client/receipts/{payment}', [ReceiptController::class, 'download']);
    });
});
