<?php

use App\Http\Controllers\Admin\AdminOrderController;
use App\Http\Controllers\Admin\FestivalContentController;
use App\Http\Controllers\Admin\GameCMSController;
use App\Http\Controllers\Admin\JazzFestivalController;
use App\Http\Controllers\Admin\SlugsController;
use App\Http\Controllers\Admin\StyleController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderExportController;
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
use App\Http\Controllers\MailController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\SignupController;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/admin/', function () {
    return redirect('/admin/dashboard');
});

Route::get('/login', [LoginController::class, 'show'])->name('loadLogin');
Route::post('/login', [LoginController::class, 'login'])->name('login');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
Route::get('/signup', [SignupController::class, 'show'])->name('loadSignup');
Route::post('/signup', [SignupController::class, 'signup'])->name('signup');

Route::get('/cart', [CartController::class, 'index'])->name('cart');
Route::post('/paymentCredentials', [CartController::class, 'paymentCredentials'])->name('paymentCredentials');
Route::get('/paymentCredentials', [CartController::class, 'paymentCredentialsRender'])->name('paymentCredentials');


Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {

    Route::get('/dashboard', [AdminPanelController::class, 'show'])->name('admin.dashboard');
    Route::get('/events', [FestivalController::class, 'index'])->name('admin.events');

    Route::post('/festivals', [FestivalController::class, 'store'])->name('admin.festivals.store');
    Route::put('/festivals/{festival}', [FestivalController::class, 'update'])->name('admin.festivals.update');
    Route::delete('/festivals/{festival}', [FestivalController::class, 'destroy'])->name('admin.festivals.destroy');
    Route::resource('festivals', FestivalController::class);

    Route::put('/festivals/{festival}/details', [FestivalController::class, 'updateDetails'])
        ->name('admin.festivals.update-details');

    Route::get('/users', [UserController::class, 'index'])->name('admin.users');
    Route::post('/users/{user}', [UserController::class, 'update'])->name('admin.users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('admin.users.destroy');
    Route::post('/users', [UserController::class, 'store'])->name('admin.users.store');

    // Orders
    Route::get('/orders', [AdminOrderController::class, 'show'])->name('admin.orders.show');

    // Orders Export
    Route::get('/orders/export', [\App\Http\Controllers\Admin\OrderAdminController::class, 'export']);

    //CMS
    Route::get('/festivals/cms/manage/{festivalId}', [FestivalContentController::class, 'show'])->name('admin.festivals.show');

    Route::post('/dashboard/homepage-content', [AdminPanelController::class, 'storeHomepageContent'])
        ->name('admin.dashboard.homepage-content.store');

    Route::post('/dashboard/hero', [AdminPanelController::class, 'uploadHero'])
        ->name('admin.dashboard.hero');

    Route::prefix('festivals/{festival}')->group(function () {
        Route::resource('jazz-festival', \App\Http\Controllers\Admin\JazzFestivalController::class)
            ->names('admin.jazz-festival');
    });

    //GAMES
    Route::put('/festivals/{festival}/game/{game}', [GameCMSController::class, 'updateGame'])
        ->name('admin.festivals.game.update');
    Route::post('/festivals/{festival}/game', [GameCMSController::class, 'storeGame'])
        ->name('admin.festivals.game.store');
    Route::delete('/festivals/{gameId}/game', [GameCMSController::class, 'deleteGame']);
});

Route::get('/api/styles', [StyleController::class, 'index'])
    ->name('styles.index');

Route::get('festivals/{festivalSlug}/{path?}', [SlugsController::class, 'show'])
    ->where('path', '.*')
    ->name('festivals.show');
Route::get('/api/festivals', [FestivalController::class, 'getFestivals']);

Route::post('/api/send-mail', [MailController::class, 'sendMail']);

Route::get('/cart', [CartController::class, 'index'])->name('cart');

Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist');

Route::get('/qr-reader', function () {
    return Inertia::render('QrReader/QrReader');
})->name('qr-reader');

/*if (Schema::hasTable('festivals')) {
    $festivals = Festival::all();

    foreach ($festivals as $festival) {
        $slug = Str::slug($festival->name, '-');


        Route::get('festivals/{festivalSlug}/{path?}', [SlugsController::class, 'show'])
            ->where('path', '.*')
            ->name('festivals.show');
    }
}*/

Route::get('/api/homepage/hero-image', function() {
    $content = \App\Models\HomepageContent::first();
    return response()->json([
        'path' => $content ? $content->hero_image_path : null
    ]);
});


