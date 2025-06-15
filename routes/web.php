<?php

use App\Http\Controllers\Admin\AdminOrderController;
use App\Http\Controllers\Admin\FestivalContentController;
use App\Http\Controllers\Admin\GameCMSController;
use App\Http\Controllers\Admin\SlugsController;
use App\Http\Controllers\Admin\StyleController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\QrReaderController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminPanelController;
use App\Http\Controllers\Admin\FestivalController;
use App\Http\Controllers\Admin\UserController;
use Inertia\Inertia;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\MailController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\SignupController;
use App\Http\Controllers\ForgetPasswordController;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Http\Middleware\QrReaderAccess;

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

Route::post('/api/send-reset-mail', [\App\Http\Controllers\ForgetPasswordController::class, 'sendResetMail'])
    ->name('send-reset-mail');

Route::get('/reset-password', [ForgetPasswordController::class, 'showResetForm']);
Route::post('/api/reset-password', [ForgetPasswordController::class, 'resetPassword'])
    ->name('reset-password');

Route::post('/api/fetch-ticket-details', [\App\Http\Controllers\QrReaderController::class, 'fetchTicketDetails']);

Route::get('/events', [EventController::class, 'index']);
Route::get('/api/events/jazz', [EventController::class, 'getJazzEvent']);
Route::get('/api/events/food', [EventController::class, 'getFoodEvent']);
Route::get('/api/events/dance', [EventController::class, 'getDanceEvent']);
Route::get('/api/events/history', [EventController::class, 'getHistoryEvent']);
Route::post('/api/events', [EventController::class, 'store']);
Route::get('/api/events/{id}', [EventController::class, 'show']);
Route::put('/api/events/{id}', [EventController::class, 'update']);
Route::delete('/api/events/{id}', [EventController::class, 'destroy']);


// Payment routes
Route::middleware(['auth'])->group(function () {
    Route::post('/api/process-payment', [PaymentController::class, 'processPayment'])->name('payment.process');
    Route::get('/payment/form', [PaymentController::class, 'showPaymentForm'])->name('payment.form');
});

// Order routes
Route::middleware(['auth'])->group(function () {
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
});

// API routes for cart operations
Route::middleware(['auth'])->group(function () {
    Route::get('/api/cart/items', [CartController::class, 'getCartItems']);
    Route::post('/api/cart/add', [CartController::class, 'addToCart']);
    Route::put('/api/cart/items/{cartItem}', [CartController::class, 'updateCartItem']);
    Route::delete('/api/cart/clear', [CartController::class, 'clearCart']);
});


// Payment routes (with authentication middleware)
Route::middleware(['auth'])->group(function () {
    Route::post('/api/create-payment-intent', [PaymentController::class, 'createPaymentIntent'])->name('payment.intent');
    Route::post('/api/process-payment', [PaymentController::class, 'processPayment'])->name('payment.process');
    Route::get('/payment/success/{order}', [PaymentController::class, 'showSuccess'])->name('payment.success');
    Route::get('/payment/failed', [PaymentController::class, 'showFailed'])->name('payment.failed');
});


Route::get('/api/homepage/hero-image', function() {
    $content = \App\Models\HomepageContent::first();
    $path = null;

    if ($content && $content->hero_image_path) {
        $path = $content->hero_image_path;
    }

    return response()->json([
        'path' => $path
    ]);
});

// QR Readers
Route::middleware(['auth', 'qr.access'])->group(function () {
    Route::get('/qr-reader', function () {
        return Inertia::render('QrReader/QrReader');
    })->name('qr-reader');

    Route::post('/api/validate-qr-code', [QrReaderController::class, 'validateQrCode']);
    Route::post('/api/fetch-ticket-details', [QrReaderController::class, 'fetchTicketDetails']);
    Route::post('/api/redeem-ticket', [QrReaderController::class, 'redeemTicket']);
});

Route::middleware(['auth'])->group(function () {
    Route::post('/api/process-payment', [PaymentController::class, 'processPayment'])->name('payment.process');
});


