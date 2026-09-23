<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ReceiptController;
use App\Livewire\AgentDashboard;
use App\Livewire\AnnouncementForm;
use App\Livewire\ClientDashboard;
use App\Livewire\ManualPayment;
use App\Livewire\PaymentForm;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

Route::middleware('auth')->group(function () {

    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    Route::middleware('role:admin')->get('/admin/dashboard', function () {
        return view('dashboard.admin');
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
