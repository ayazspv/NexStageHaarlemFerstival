<?php

use App\Http\Controllers\Admin\CMSController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminLoginController;
use App\Http\Controllers\Admin\AdminPanelController;
use App\Http\Controllers\Admin\FestivalController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\LoginController;

Route::get('/', [HomeController::class, 'index'])->name('home');

// Change that to just login later for customers
/* Route::get('/login', function () {
    return redirect('/admin/login');
})->name('login'); */

Route::get('/admin/', function () {
    return redirect('/admin/dashboard');
});


//Login routing for admins
/*Route::get('/admin/login', [AdminLoginController::class, 'show'])->name('admin.login');
Route::post('/admin/login', [AdminLoginController::class, 'login']);
Route::post('/admin/logout', [AdminLoginController::class, 'logout'])->name('admin.logout');*/

//Login routing for normal users
Route::get('/login', [LoginController::class, 'show'])->name('loadLogin');
Route::post('/login', [LoginController::class, 'login'])->name('login');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminPanelController::class, 'show'])->name('admin.dashboard');
    Route::get('/events', [FestivalController::class, 'index'])->name('admin.events');

    Route::post('/festivals', [FestivalController::class, 'store'])->name('admin.festivals.store');
    Route::post('/festivals/{festival}', [FestivalController::class, 'update'])->name('admin.festivals.update');
    Route::delete('/festivals/{festival}', [FestivalController::class, 'destroy'])->name('admin.festivals.destroy');
    Route::resource('festivals', FestivalController::class);

    Route::get('/festivals/cms/manage/{festival}', [CMSController::class, 'cmsManage'])->name('admin.festivals.manage');
    Route::patch('/festivals/cms/{festival}', [CMSController::class, 'cmsUpdate'])
        ->name('admin.events.update');
    Route::patch('/festivals/cms/{festival}/remove-content', [CMSController::class, 'cmsRemoveContent'])
        ->name('admin.events.removeContent');

    // New route to create a subpage (will create a new CMS record and redirect)
    Route::get('/festivals/cms/create-subpage/{festival}/{parent}', [CMSController::class, 'createSubpage'])
        ->name('admin.festivals.subpage.create');

    // Show subpage editor.
    Route::get('/festivals/cms/edit-subpage/{festival}/{cms}', [CMSController::class, 'editSubpage'])
        ->name('admin.festivals.subpage.edit');

    // Update subpage (from editor).
    Route::patch('/festivals/cms/edit-subpage/{festival}/{cms}', [CMSController::class, 'cmsUpdateSubpage'])
        ->name('admin.festivals.subpage.update');

    Route::get('/users', [UserController::class, 'index'])->name('admin.users');
    Route::post('/users/{user}', [UserController::class, 'update'])->name('admin.users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('admin.users.destroy');
    Route::post('/users', [UserController::class, 'store'])->name('admin.users.store');
});
