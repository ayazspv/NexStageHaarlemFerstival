<?php

use App\Http\Controllers\Admin\CMSController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\GameCMSController;
use App\Http\Controllers\Admin\SlugsController;
use App\Http\Controllers\Admin\StyleController;
use App\Http\Controllers\HomeController;
use App\Models\CMS;
use App\Models\Festival;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminLoginController;
use App\Http\Controllers\Admin\AdminPanelController;
use App\Http\Controllers\Admin\FestivalController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Inertia\Inertia;
use App\Http\Controllers\LoginController;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/admin/', function () {
    return redirect('/admin/dashboard');
});

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

    Route::get('/festivals/cms/create-subpage/{festival}/{parent}', [CMSController::class, 'createSubpage'])
        ->name('admin.festivals.subpage.create');

    Route::get('/festivals/cms/edit-subpage/{festival}/{cms}', [CMSController::class, 'editSubpage'])
        ->name('admin.festivals.subpage.edit');

    Route::patch('/festivals/cms/edit-subpage/{festival}/{cms}', [CMSController::class, 'cmsUpdateSubpage'])
        ->name('admin.festivals.subpage.update');

    Route::get('/users', [UserController::class, 'index'])->name('admin.users');
    Route::post('/users/{user}', [UserController::class, 'update'])->name('admin.users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('admin.users.destroy');
    Route::post('/users', [UserController::class, 'store'])->name('admin.users.store');

    //GAMES
    Route::put('/festivals/{festival}/game/{game}', [GameCMSController::class, 'updateGame'])
        ->name('admin.festivals.game.update');
    Route::post('/festivals/{festival}/game', [GameCMSController::class, 'storeGame'])
        ->name('admin.festivals.game.store');
});

Route::get('/api/styles', [StyleController::class, 'index'])
    ->name('styles.index');

Route::get('festivals/{festivalSlug}/{path?}', [SlugsController::class, 'show'])
    ->where('path', '.*')
    ->name('festivals.show');

/*if (Schema::hasTable('festivals')) {
    $festivals = Festival::all();

    foreach ($festivals as $festival) {
        $slug = Str::slug($festival->name, '-');


        Route::get('festivals/{festivalSlug}/{path?}', [SlugsController::class, 'show'])
            ->where('path', '.*')
            ->name('festivals.show');
    }
}*/
