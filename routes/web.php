<?php

use App\Http\Controllers\Admin\AdminAnnouncementController;
use App\Http\Controllers\Admin\AdminAuthorController;
use App\Http\Controllers\Admin\AdminUploadController;
use App\Http\Controllers\Admin\AdminBlogController;
use App\Http\Controllers\Admin\AdminCouponController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminEventController;
use App\Http\Controllers\Admin\AdminMusicCollectionController;
use App\Http\Controllers\Admin\AdminMusicController;
use App\Http\Controllers\Admin\AdminTemplateController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Middleware\EnsureEventNotExpired;
use App\Http\Controllers\Dashboard\EventController;
use App\Http\Controllers\Dashboard\EventEditorController;
use App\Http\Controllers\Dashboard\GuestController;
use App\Http\Controllers\Dashboard\EventTemplateController;
use App\Http\Controllers\Dashboard\DashboardWishController;
use App\Http\Controllers\Dashboard\RsvpDashboardController;
use App\Http\Controllers\Dashboard\BackupController;
use App\Http\Controllers\Dashboard\PaymentController;
use App\Http\Controllers\Dashboard\QuotaController;
use App\Http\Controllers\Dashboard\SendInvitationController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\SelfRegisterController;
use App\Http\Controllers\UnsubscribeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RsvpController;
use App\Http\Controllers\TemplatePreviewController;
use App\Http\Controllers\WishController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// ─── Public: trang thiệp (Blade, server-side render) ─────────────────────────
Route::get('/thiep/{slug}',              [InvitationController::class, 'show'])->name('invitation.show');
Route::get('/thiep/{slug}/calendar.ics', [InvitationController::class, 'calendar'])->name('invitation.calendar');
Route::get( '/thiep/{slug}/rsvp',         [RsvpController::class, 'form'])->name('invitation.rsvp.form');
Route::post('/thiep/{slug}/rsvp',         [RsvpController::class, 'store'])->middleware('throttle:rsvp')->name('invitation.rsvp.store');
Route::get( '/thiep/{slug}/rsvp/success', [RsvpController::class, 'success'])->name('invitation.rsvp.success');
Route::get( '/thiep/{slug}/wishes',       [WishController::class, 'index'])->name('invitation.wishes.index');
Route::post('/thiep/{slug}/wishes',       [WishController::class, 'store'])->middleware('throttle:wishes')->name('invitation.wishes.store');
Route::get( '/unsubscribe/{token}',       [UnsubscribeController::class, 'handle'])->name('unsubscribe');

// Self-register — public
Route::get( '/thiep/{slug}/dang-ky',         [SelfRegisterController::class, 'form'])->name('invitation.register.form');
Route::post('/thiep/{slug}/dang-ky',         [SelfRegisterController::class, 'store'])->name('invitation.register.store');
Route::get( '/thiep/{slug}/dang-ky/success', [SelfRegisterController::class, 'success'])->name('invitation.register.success');

// ─── Blog (public, Blade SSR) ─────────────────────────────────────────────────
Route::get('/blog',         [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{slug}',  [BlogController::class, 'show'])->name('blog.show');

// ─── Template preview (public, sample data) ───────────────────────────────────
Route::get('/template-preview/{template}', [TemplatePreviewController::class, 'show'])->name('template.preview');

// ─── Google OAuth ─────────────────────────────────────────────────────────────
Route::get('/auth/google',          [GoogleController::class, 'redirect'])->name('auth.google');
Route::get('/auth/google/callback', [GoogleController::class, 'callback'])->name('auth.google.callback');

// ─── Health check ─────────────────────────────────────────────────────────────
Route::get('/health', function () {
    $checks = [];

    try { \DB::select('SELECT 1'); $checks['db'] = 'ok'; } catch (\Throwable) { $checks['db'] = 'error'; }

    if (config('queue.default') === 'redis') {
        try { \Illuminate\Support\Facades\Redis::ping(); $checks['redis'] = 'ok'; } catch (\Throwable) { $checks['redis'] = 'error'; }
    }

    try { \Illuminate\Support\Facades\Storage::disk()->exists('.health'); $checks['storage'] = 'ok'; } catch (\Throwable) { $checks['storage'] = 'error'; }

    $status = in_array('error', $checks) ? 503 : 200;
    return response()->json(['status' => $status === 200 ? 'ok' : 'degraded', 'checks' => $checks], $status);
})->name('health');

// ─── Dashboard (Inertia + Vue) ────────────────────────────────────────────────
Route::get('/', function () {
    if (auth()->check()) return redirect()->route('dashboard');
    return Inertia::render('Welcome', [
        'canLogin'    => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]);
})->name('home');

Route::get('/dashboard', function () {
    if (auth()->user()->hasRole('admin')) {
        return redirect()->route('admin.dashboard');
    }
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified', 'role:host|admin', EnsureEventNotExpired::class])
    ->prefix('dashboard')
    ->name('dashboard.')
    ->group(function () {
        Route::get('/events/create',                   [EventController::class, 'create'])->name('events.create');
        Route::post('/events',                         [EventController::class, 'store'])->name('events.store');
        Route::get('/events/{slug}',                   [EventController::class, 'show'])->name('events.show');
        Route::get('/events/{event:slug}/template',    [EventTemplateController::class, 'edit'])->name('events.template.edit');
        Route::patch('/events/{event:slug}/template',  [EventTemplateController::class, 'update'])->name('events.template.update');

        Route::get(   '/events/{event:slug}/guests',         [GuestController::class, 'index'])->name('events.guests.index');
        Route::post(  '/events/{event:slug}/guests',         [GuestController::class, 'store'])->name('events.guests.store');
        Route::patch( '/events/{event:slug}/guests/{guest}', [GuestController::class, 'update'])->name('events.guests.update');
        Route::delete('/events/{event:slug}/guests/{guest}', [GuestController::class, 'destroy'])->name('events.guests.destroy');
        Route::post(  '/events/{event:slug}/guests/import',  [GuestController::class, 'import'])->name('events.guests.import');
        Route::get(   '/events/{event:slug}/guests/export',  [GuestController::class, 'export'])->name('events.guests.export');

        Route::get(  '/events/{event:slug}/editor',   [EventEditorController::class, 'show'])->name('events.editor');
        Route::patch('/events/{event:slug}/settings', [EventEditorController::class, 'saveSettings'])->name('events.settings.save');
        Route::get(  '/events/{event:slug}/preview',  [EventEditorController::class, 'preview'])->name('events.preview');

        Route::get( '/events/{event:slug}/send',          [SendInvitationController::class, 'index'])->name('events.send.index');
        Route::post('/events/{event:slug}/send',          [SendInvitationController::class, 'send'])->name('events.send.store');
        Route::get( '/events/{event:slug}/send/progress',[SendInvitationController::class, 'progress'])->name('events.send.progress');

        Route::get(   '/events/{event:slug}/rsvp',              [RsvpDashboardController::class, 'index'])->name('events.rsvp.index');
        Route::get(   '/events/{event:slug}/rsvp/export',       [RsvpDashboardController::class, 'export'])->name('events.rsvp.export');
        Route::patch( '/events/{event:slug}/rsvp/{rsvp}/table', [RsvpDashboardController::class, 'assignTable'])->name('events.rsvp.table');

        Route::get(   '/events/{event:slug}/wishes',                 [DashboardWishController::class, 'index'])->name('events.wishes.index');
        Route::get(   '/events/{event:slug}/wishes/export-pdf',    [DashboardWishController::class, 'exportPdf'])->name('events.wishes.export-pdf');
        Route::patch( '/events/{event:slug}/wishes/{wish}/pin',    [DashboardWishController::class, 'pin'])->name('events.wishes.pin');
        Route::patch( '/events/{event:slug}/wishes/{wish}/hide',   [DashboardWishController::class, 'hide'])->name('events.wishes.hide');
        Route::delete('/events/{event:slug}/wishes/{wish}',        [DashboardWishController::class, 'destroy'])->name('events.wishes.destroy');

        Route::post('/events/{event:slug}/backup',               [BackupController::class, 'create'])->name('events.backup.create');
        Route::get( '/events/{event:slug}/backup/status',        [BackupController::class, 'status'])->name('events.backup.status');
        Route::get( '/events/{event:slug}/backup/{token}',       [BackupController::class, 'download'])->name('events.backup.download');

        Route::get( '/events/{event:slug}/upgrade',                          [PaymentController::class, 'upgradePage'])->name('events.upgrade');
        Route::post('/events/{event:slug}/payment',                          [PaymentController::class, 'create'])->name('events.payment.create');
        Route::get( '/events/{event:slug}/payment/return',                   [PaymentController::class, 'returnHandler'])->name('events.payment.return');
        Route::get( '/events/{event:slug}/payment/cancel',                   [PaymentController::class, 'cancelHandler'])->name('events.payment.cancel');
        Route::post('/events/{event:slug}/payment/coupon-preview',           [PaymentController::class, 'couponPreview'])->name('events.payment.coupon-preview');
        Route::get( '/events/{event:slug}/quota',                            [QuotaController::class, 'show'])->name('events.quota');
    });

// ─── Admin Panel ─────────────────────────────────────────────────────────────
Route::middleware(['auth', 'verified', 'role:admin'])
    ->prefix('admin')->name('admin.')
    ->group(function () {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

        Route::get(   '/users',             [AdminUserController::class, 'index'])  ->name('users.index');
        Route::patch( '/users/{user}/role', [AdminUserController::class, 'role'])   ->name('users.role');
        Route::patch( '/users/{user}/ban',  [AdminUserController::class, 'ban'])    ->name('users.ban');
        Route::patch( '/users/{user}/unban',[AdminUserController::class, 'unban'])  ->name('users.unban');
        Route::delete('/users/{user}',      [AdminUserController::class, 'destroy'])->name('users.destroy');

        Route::get(   '/templates',             [AdminTemplateController::class, 'index'])  ->name('templates.index');
        Route::post(  '/templates',             [AdminTemplateController::class, 'store'])  ->name('templates.store');
        Route::patch( '/templates/{template}',  [AdminTemplateController::class, 'update']) ->name('templates.update');
        Route::delete('/templates/{template}',  [AdminTemplateController::class, 'destroy'])->name('templates.destroy');

        Route::get(   '/music',                [AdminMusicController::class, 'index'])  ->name('music.index');
        Route::post(  '/music',                [AdminMusicController::class, 'store'])  ->name('music.store');
        Route::get(   '/music/{track}/stream', [AdminMusicController::class, 'stream']) ->name('music.stream');
        Route::get(   '/music/{track}/cover',  [AdminMusicController::class, 'cover'])  ->name('music.cover');
        Route::patch( '/music/{track}',        [AdminMusicController::class, 'update']) ->name('music.update');
        Route::delete('/music/{track}',        [AdminMusicController::class, 'destroy'])->name('music.destroy');

        Route::post(  '/music/collections',                                  [AdminMusicCollectionController::class, 'store'])      ->name('music.collections.store');
        Route::get(   '/music/collections/{collection}/cover',               [AdminMusicCollectionController::class, 'cover'])      ->name('music.collections.cover');
        Route::patch( '/music/collections/{collection}',                     [AdminMusicCollectionController::class, 'update'])     ->name('music.collections.update');
        Route::delete('/music/collections/{collection}',                     [AdminMusicCollectionController::class, 'destroy'])    ->name('music.collections.destroy');
        Route::post(  '/music/collections/{collection}/tracks',              [AdminMusicCollectionController::class, 'addTracks'])  ->name('music.collections.tracks.add');
        Route::delete('/music/collections/{collection}/tracks/{track}',      [AdminMusicCollectionController::class, 'removeTrack'])->name('music.collections.tracks.remove');
        Route::post(  '/music/collections/{collection}/reorder',             [AdminMusicCollectionController::class, 'reorder'])    ->name('music.collections.reorder');

        Route::get(   '/events',         [AdminEventController::class, 'index'])  ->name('events.index');
        Route::delete('/events/{event}', [AdminEventController::class, 'destroy'])->name('events.destroy');

        Route::get(   '/coupons',          [AdminCouponController::class, 'index'])  ->name('coupons.index');
        Route::post(  '/coupons',          [AdminCouponController::class, 'store'])  ->name('coupons.store');
        Route::patch( '/coupons/{coupon}', [AdminCouponController::class, 'update']) ->name('coupons.update');
        Route::delete('/coupons/{coupon}', [AdminCouponController::class, 'destroy'])->name('coupons.destroy');

        Route::get(   '/blog',              [AdminBlogController::class, 'index'])  ->name('blog.index');
        Route::get(   '/blog/create',       [AdminBlogController::class, 'create']) ->name('blog.create');
        Route::post(  '/blog',              [AdminBlogController::class, 'store'])  ->name('blog.store');
        Route::get(   '/blog/{post}/edit',  [AdminBlogController::class, 'edit'])   ->name('blog.edit');
        Route::patch( '/blog/{post}',       [AdminBlogController::class, 'update']) ->name('blog.update');
        Route::delete('/blog/{post}',       [AdminBlogController::class, 'destroy'])->name('blog.destroy');

        Route::get(   '/authors',        [AdminAuthorController::class, 'index']) ->name('authors.index');
        Route::post(  '/authors',        [AdminAuthorController::class, 'store']) ->name('authors.store');
        Route::patch( '/authors/{user}', [AdminAuthorController::class, 'update'])->name('authors.update');
        Route::delete('/authors/{user}', [AdminAuthorController::class, 'destroy'])->name('authors.destroy');

        Route::post('/upload/image', [AdminUploadController::class, 'image'])->name('upload.image');

        Route::get(   '/announcements',       [AdminAnnouncementController::class, 'index'])  ->name('announcements.index');
        Route::post(  '/announcements',       [AdminAnnouncementController::class, 'store'])  ->name('announcements.store');
        Route::patch( '/announcements/{ann}', [AdminAnnouncementController::class, 'update']) ->name('announcements.update');
        Route::delete('/announcements/{ann}', [AdminAnnouncementController::class, 'destroy'])->name('announcements.destroy');
    });

// PayOS webhook — ngoài dashboard group, không có auth/CSRF
Route::post('/webhooks/payos', [PaymentController::class, 'webhook'])->name('webhooks.payos');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
