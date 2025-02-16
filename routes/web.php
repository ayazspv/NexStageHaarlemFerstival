<?php

use App\Http\Controllers\Admin\EventController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminLoginController;
use App\Http\Controllers\Admin\AdminPanelController;
use Inertia\Inertia;
use App\Http\Controllers\HomeController;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/event/{id}', [HomeController::class, 'show'])->name('event.show');

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
    Route::get('/events', [EventController::class, 'show'])->name('admin.events');
});
