<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
})->name('home');

use App\Http\Controllers\BusinessController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\BudgetController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::middleware(['permission:view reports'])->group(function () {
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('budgets', [BudgetController::class, 'index'])->name('budgets.index');
        Route::get('budgets/setup', [BudgetController::class, 'create'])->name('budgets.create');
        Route::post('budgets', [BudgetController::class, 'store'])->name('budgets.store');
    });
    
    Route::middleware(['permission:manage settings'])->group(function () {
        Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit_logs.index');
    });

    Route::resource('businesses', BusinessController::class);
    Route::resource('services', ServiceController::class);
    
    Route::post('services/{service}/modules', [App\Http\Controllers\ModuleController::class, 'store'])->name('services.modules.store');
    Route::put('modules/{module}', [App\Http\Controllers\ModuleController::class, 'update'])->name('modules.update');
    Route::delete('modules/{module}', [App\Http\Controllers\ModuleController::class, 'destroy'])->name('modules.destroy');

    Route::post('services/{service}/packages', [App\Http\Controllers\PackageController::class, 'store'])->name('services.packages.store');
    Route::put('packages/{package}', [App\Http\Controllers\PackageController::class, 'update'])->name('packages.update');
    Route::delete('packages/{package}', [App\Http\Controllers\PackageController::class, 'destroy'])->name('packages.destroy');

    Route::get('subscriptions/{subscription}/renew', [SubscriptionController::class, 'renewForm'])->name('subscriptions.renewForm');
    Route::post('subscriptions/{subscription}/renew', [SubscriptionController::class, 'renew'])->name('subscriptions.renew');
    Route::post('subscriptions/{id}/restore', [SubscriptionController::class, 'restore'])->name('subscriptions.restore');
    Route::post('subscriptions/{subscription}/collections', [App\Http\Controllers\SubscriptionCollectionController::class, 'store'])->name('subscriptions.collections.store');
    Route::delete('collections/{collection}', [App\Http\Controllers\SubscriptionCollectionController::class, 'destroy'])->name('subscriptions.collections.destroy');
    Route::resource('subscriptions', SubscriptionController::class);

    Route::middleware(['role:Admin'])->group(function () {
        Route::patch('staff/{staff}/toggle', [StaffController::class, 'toggleActive'])->name('staff.toggle');
        Route::resource('staff', StaffController::class)->except(['destroy']);
    });
});

require __DIR__.'/settings.php';
