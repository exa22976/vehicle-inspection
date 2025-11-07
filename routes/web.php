<?php

use Illuminate\Support\Facades\Route;
// Auth
use App\Http\Controllers\Auth\LoginController;
// Admin
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\InspectionRequestController;
use App\Http\Controllers\Admin\VehicleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\InspectionPatternController;
use App\Http\Controllers\Admin\InspectionItemController;
use App\Http\Controllers\Admin\InspectionRecordController;
use App\Http\Controllers\Admin\DepartmentController;
// Public
use App\Http\Controllers\InspectionController;

// Auth Routes
Route::get('/', function () {
    return redirect()->route('login');
});

// Auth Routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Admin Routes
Route::prefix('admin')->middleware('auth')->name('admin.')->group(function () {
    // Dashboard & Inspection Requests
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/inspection-requests', [InspectionRequestController::class, 'store'])->name('inspection-requests.store');
    Route::delete('/inspection-requests/{inspectionRequest}', [InspectionRequestController::class, 'destroy'])->name('inspection-requests.destroy');

    // Inspection Records (Details & Re-request)
    Route::get('/records/{inspectionRecord}', [InspectionRecordController::class, 'show'])->name('records.show');
    Route::put('/records/{inspectionRecord}/status', [InspectionRecordController::class, 'updateStatus'])->name('records.updateStatus');
    Route::post('/records/{inspectionRecord}/re-request', [InspectionRecordController::class, 'reRequest'])->name('records.reRequest');

    // Master Management
    // -- Vehicles (with CSV routes)
    Route::get('/vehicles/export', [VehicleController::class, 'export'])->name('vehicles.export');
    Route::post('/vehicles/import', [VehicleController::class, 'import'])->name('vehicles.import');
    Route::resource('vehicles', VehicleController::class);

    // -- Users (with CSV routes)
    Route::get('/users/export', [UserController::class, 'export'])->name('users.export');
    Route::post('/users/import', [UserController::class, 'import'])->name('users.import');
    Route::resource('users', UserController::class)->except(['show']);

    Route::get('/departments', [\App\Http\Controllers\Admin\DepartmentController::class, 'index'])->name('departments.index');
    Route::post('/departments', [\App\Http\Controllers\Admin\DepartmentController::class, 'store'])->name('departments.store');
    Route::put('/departments/{department}', [\App\Http\Controllers\Admin\DepartmentController::class, 'update'])->name('departments.update');
    Route::delete('/departments/{department}', [\App\Http\Controllers\Admin\DepartmentController::class, 'destroy'])->name('departments.destroy');

    // -- Inspection Patterns (with nested Item routes)
    Route::resource('patterns', InspectionPatternController::class)->except(['show']);
    Route::resource('patterns.items', InspectionItemController::class)->only(['store'])->shallow();
    Route::resource('items', InspectionItemController::class)->only(['update', 'destroy'])->shallow();
});

// Public Inspection Form Route
Route::get('/inspection/{token}', [InspectionController::class, 'showForm'])->name('inspection.form');
Route::post('/inspection/{token}', [InspectionController::class, 'submitForm'])->name('inspection.submit');
Route::get('/inspection/complete', [\App\Http\Controllers\InspectionController::class, 'complete'])->name('inspection.complete');
Route::get('/inspection/invalid', [\App\Http\Controllers\InspectionController::class, 'invalid'])->name('inspection.invalid');
