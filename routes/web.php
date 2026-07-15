<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminMerchandiseController;
use App\Http\Controllers\AdminProgramController;
use App\Http\Controllers\BankAccountController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpeditionController;
use App\Http\Controllers\FilmController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\LandingProgramController;
use App\Http\Controllers\MerchandiseCategoryController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PublicStorageController;
use App\Http\Controllers\ProgramCategoryController;
use App\Http\Controllers\SubmissionReviewController;
use App\Http\Controllers\SubmissionSettingController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserDetailController;
use App\Models\SubmissionSetting;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Laravolt\Indonesia\Models\Province;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/storage/{path}', [PublicStorageController::class, 'show'])
    ->where('path', '.*')
    ->name('storage.public');

// Landing
Route::get('/', [LandingController::class, 'home'])->name('landing.home');
Route::get('/program', [LandingController::class, 'program'])->name('landing.program');
Route::get('/programs', [LandingProgramController::class, 'index'])->name('programs.index');
Route::get('/programs/{program:slug}', [LandingProgramController::class, 'show'])->name('programs.show');
Route::get('/umum', function () {
    return view('landing.kategori.umum');
});
Route::get('/pelajar', function () {
    return view('landing.kategori.pelajar');
});
Route::get('/ekshibisi', function () {
    return view('landing.kategori.ekshibisi');
});
Route::get('/ekshibisi/organisasi', function () {
    return view('landing.kategori.ekshibisis.organisasi');
});
Route::get('/ekshibisi/paud', function () {
    return view('landing.kategori.ekshibisis.paud');
});
Route::get('/download/ekatalog', function () {
    $setting = SubmissionSetting::current();
    $catalogFile = optional($setting)->last_year_catalog_file;
    $legacyCatalogUrl = optional($setting)->last_year_catalog_url;

    \App\Models\DownloadLog::updateOrCreate(
        ['ip_address' => request()->ip()],
        [
            'file'       => basename($catalogFile ?: 'ekatalog-2025.pdf'),
            'user_agent' => request()->userAgent(),
            'user_id'    => auth()->id() ?? null,
        ]
    );

    if ($catalogFile) {
        return response()->download(storage_path('app/public/' . ltrim($catalogFile, '/')));
    }

    if ($legacyCatalogUrl) {
        if (Str::startsWith($legacyCatalogUrl, ['http://', 'https://'])) {
            return redirect()->away($legacyCatalogUrl);
        }

        return redirect(Str::startsWith($legacyCatalogUrl, '/') ? $legacyCatalogUrl : url($legacyCatalogUrl));
    }

    $filePath = public_path('landing/pdf/ekatalog-2025.pdf');
    return response()->download($filePath);
})->name('download.ekatalog');
Route::get('/merchandise', [LandingController::class, 'merchandise'])->name('merchandise');
Route::get('/merchandise/biodata', function () {
    return redirect()->route('user-detail.index');
});

//? CRUD Pages :

Route::get('/login', [AuthController::class, 'index'])->name('login')->middleware('guest');
Route::get('/register', [AuthController::class, 'register'])->name('register')->middleware('guest');
Route::post('/logout', [AuthController::class, 'logout']);
Route::post('/authenticate', [AuthController::class, 'authenticate'])->name('authenticate');
Route::post('/regist', [AuthController::class, 'registStore'])->name('registStore');
Route::get('/dashboard', [DashboardController::class, 'index'])->middleware('auth')->name('dashboard');
Route::get('/setting/submission', [SubmissionSettingController::class, 'index'])->middleware(['auth', 'role:admin'])->name('settingIndex');
Route::post('/setting/submission', [SubmissionSettingController::class, 'store'])->middleware(['auth', 'role:admin'])->name('settingStore');
Route::put('/setting/submission/{submissionSetting}', [SubmissionSettingController::class, 'update'])->middleware(['auth', 'role:admin'])->name('settingUpdate');
Route::put('/setting/submission/{submissionSetting}/landing', [SubmissionSettingController::class, 'updateLanding'])->middleware(['auth', 'role:admin'])->name('settingLandingUpdate');
Route::get('/setting/submission/{submissionSetting}/edit', [SubmissionSettingController::class, 'edit'])->middleware(['auth', 'role:admin'])->name('settingEdit');
Route::delete('/setting/submission/{submissionSetting}', [SubmissionSettingController::class, 'destroy'])->middleware(['auth', 'role:admin'])->name('settingDestroy');
Route::post('/setting/general', [SubmissionSettingController::class, 'updateGeneral'])->middleware(['auth', 'role:admin'])->name('settingGeneralUpdate');

// User
Route::resource('/users', UserController::class)->middleware('auth');
Route::get('/users/index/author', [UserController::class, 'indexAuth'])->middleware('auth')->name('users.index.author');
Route::get('/users/index/author/create', [UserController::class, 'createAuthor'])->middleware('auth')->name('users.create.author');
Route::get('/users/index/kurator', [UserController::class, 'indexKur'])->middleware('auth')->name('users.index.kurator');
Route::get('/changepass', [UserController::class, 'changePass'])->name('user.changepass');
Route::post('/updatepass', [UserController::class, 'updatePassword'])->name('user.changepass.update');

// User Detail
Route::get('/biodata', [UserDetailController::class, 'index'])->name('user-detail.index')->middleware('auth');
Route::post('/biodata', [UserDetailController::class, 'save'])->name('user-detail.save')->middleware('auth');

// Film
Route::resource('film', FilmController::class)->middleware('auth');
Route::get('/film/{id}/gsm/download', [FilmController::class, 'downloadGsm'])->name('film.gsm.download');

// Category
Route::resource('/categories', CategoryController::class)->middleware('auth');

Route::middleware('auth')->group(function () {
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/{merchandise}', [CartController::class, 'store'])->name('cart.store');
    Route::put('/cart/item/{cartItem}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/item/{cartItem}', [CartController::class, 'destroy'])->name('cart.destroy');

    Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout.show');
    Route::get('/checkout/destination-search', [CheckoutController::class, 'searchDestination'])->name('checkout.destination-search');
    Route::post('/checkout/shipping-options', [CheckoutController::class, 'shippingOptions'])->name('checkout.shipping-options');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');

    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{order}/payment-proof', [OrderController::class, 'uploadPaymentProof'])->name('orders.payment-proof');

    Route::get('/review/submissions', [SubmissionReviewController::class, 'index'])
        ->middleware('role:admin,adminsub,kurator,juri')
        ->name('review.index');
    Route::post('/review/submissions/start-curation', [SubmissionReviewController::class, 'startCuration'])
        ->middleware('role:admin,adminsub')
        ->name('review.start-curation');
    Route::post('/review/submissions/official-selection', [SubmissionReviewController::class, 'setOfficialSelection'])
        ->middleware('role:admin,adminsub')
        ->name('review.official-selection');
    Route::patch('/review/submissions/{film}/status', [SubmissionReviewController::class, 'updateCurationStatus'])
        ->middleware('role:admin,adminsub')
        ->name('review.status');
    Route::get('/review/submissions/{film}/{stage}/score', [SubmissionReviewController::class, 'score'])
        ->middleware('role:kurator,juri')
        ->name('review.score');
    Route::patch('/review/submissions/{film}/{stage}/score', [SubmissionReviewController::class, 'storeScore'])
        ->middleware('role:kurator,juri')
        ->name('review.score.update');
    Route::patch('/review/submissions/{film}/winner-rank', [SubmissionReviewController::class, 'updateWinnerRank'])
        ->middleware('role:admin,adminsub')
        ->name('review.winner-rank');
    Route::patch('/review/submissions/{film}/nominate', [SubmissionReviewController::class, 'updateNominate'])
        ->middleware('role:admin,adminsub')
        ->name('review.nominate');
});

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/expeditions/origin/laravolt-search', [ExpeditionController::class, 'searchLaravoltOrigin'])->name('expeditions.origin.laravolt-search');
    Route::get('/expeditions/origin/rajaongkir-search', [ExpeditionController::class, 'searchRajaOngkirOrigin'])->name('expeditions.origin.rajaongkir-search');
    Route::post('/expeditions/origin/laravolt', [ExpeditionController::class, 'updateOriginFromLaravolt'])->name('expeditions.origin.laravolt');
    Route::post('/expeditions/origin/rajaongkir', [ExpeditionController::class, 'updateOriginFromRajaOngkir'])->name('expeditions.origin.rajaongkir');
    Route::resource('/merchandise-categories', MerchandiseCategoryController::class)->except('show');
    Route::resource('/admin-merchandises', AdminMerchandiseController::class)->except('show');
    Route::resource('/program-categories', ProgramCategoryController::class)->except('show');
    Route::resource('/admin-programs', AdminProgramController::class)->except('show');
    Route::resource('/expeditions', ExpeditionController::class)->except('show');
    Route::resource('/bank-accounts', BankAccountController::class)->except('show');
});

Route::middleware(['auth', 'role:admin,adminsub'])->group(function () {
    Route::get('/admin/orders', [OrderController::class, 'adminIndex'])->name('admin.orders.index');
    Route::get('/admin/orders/{order}', [OrderController::class, 'adminShow'])->name('admin.orders.show');
    Route::post('/admin/orders/{order}/verify', [OrderController::class, 'verify'])->name('admin.orders.verify');
    Route::post('/admin/orders/{order}/reject', [OrderController::class, 'reject'])->name('admin.orders.reject');
    Route::patch('/admin/orders/{order}/airway-bill', [OrderController::class, 'updateAirwayBill'])->name('admin.orders.airway-bill.update');
    Route::post('/admin/orders/{order}/shipment', [OrderController::class, 'createShipment'])->name('admin.orders.shipment.store');
    Route::post('/admin/orders/{order}/shipment/sync', [OrderController::class, 'syncShipment'])->name('admin.orders.shipment.sync');
});
