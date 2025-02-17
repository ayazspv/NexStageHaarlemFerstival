<?php

use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminLoginController;
use App\Http\Controllers\Admin\AdminPanelController;
use App\Http\Controllers\Admin\FestivalController;
use Inertia\Inertia;

Route::get('/', [HomeController::class, 'index'])->name('home');

// ✅ Fix: Define a global login route
Route::get('/login', function () {
    return redirect('/admin/login'); // Redirects to admin login
})->name('login');

Route::get('/admin/', function () {
    return redirect('/admin/dashboard');
});

Route::get('/admin/login', [AdminLoginController::class, 'show'])->name('admin.login');
Route::post('/admin/login', [AdminLoginController::class, 'login']);
Route::post('/admin/logout', [AdminLoginController::class, 'logout'])->name('admin.logout');

// ✅ Protect admin routes with auth + admin middleware
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminPanelController::class, 'show'])->name('admin.dashboard');
    Route::get('/events', [FestivalController::class, 'index'])->name('admin.events');
    Route::post('/events', [EventController::class, 'store'])->name('admin.events.store');
    Route::put('/events/{event}', [EventController::class, 'update'])->name('admin.events.update');
    Route::delete('/events/{event}', [EventController::class, 'destroy'])->name('admin.events.destroy');
    Route::post('/festivals', [FestivalController::class, 'store'])->name('admin.festivals.store');
    Route::post('/festivals/{festival}', [FestivalController::class, 'update'])->name('admin.festivals.update');
    Route::delete('/festivals/{festival}', [FestivalController::class, 'destroy'])->name('admin.festivals.destroy');
    Route::resource('festivals', FestivalController::class);
});
