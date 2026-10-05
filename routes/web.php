<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\MemberRegistrationController;
use App\Http\Controllers\PushSubscriptionController;
use App\Http\Controllers\ReceiptController;
use App\Livewire\AdminDashboard;
use App\Livewire\AgentDashboard;
use App\Livewire\AnnouncementForm;
use App\Livewire\ClientDashboard;
use App\Livewire\ClientLoanRequest;
use App\Livewire\LoanCreate;
use App\Livewire\LoanSchedulePaymentForm;
use App\Livewire\ManualLoanPayment;
use App\Livewire\ManualPayment;
use App\Livewire\PaymentForm;
use App\Livewire\UserManagement;
use App\Models\LoanType;
use Illuminate\Support\Facades\Route;

Route::view('/', 'home')->name('home');
Route::get('/credits', fn () => view('credits.index', [
    'loanTypes' => LoanType::where('is_active', true)->orderBy('id')->get(),
]))->name('credits.index');
Route::view('/epargne', 'savings.index')->name('savings.index');

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
        Route::get('/admin/loans/create', LoanCreate::class);
    });

    // ------------------------------------------------------------
    // ESPACE AGENT
    // ------------------------------------------------------------
    Route::middleware('role:agent')->group(function () {
        Route::get('/agent/dashboard', AgentDashboard::class);
        Route::get('/agent/loans/create', LoanCreate::class);
        Route::get('/agent/manual-payment/{schedule}', ManualPayment::class);
        Route::get('/agent/manual-loan-payment/{schedule}', ManualLoanPayment::class);
        Route::get('/agent/announcements', AnnouncementForm::class);
        Route::get('/agent/receipts/{payment}', [ReceiptController::class, 'download']);
    });

    // ------------------------------------------------------------
    // ESPACE CLIENT
    // ------------------------------------------------------------
    Route::middleware('role:client')->group(function () {
        Route::get('/client/dashboard', ClientDashboard::class);
        Route::get('/client/loans/create', ClientLoanRequest::class);
        Route::get('/client/payment/{schedule}', PaymentForm::class);
        Route::get('/client/loan-payment/{schedule}', LoanSchedulePaymentForm::class);
        Route::get('/client/receipts/{payment}', [ReceiptController::class, 'download']);
    });
});
