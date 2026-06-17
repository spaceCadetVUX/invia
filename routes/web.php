<?php

use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\Dashboard\EventController;
use App\Http\Controllers\Dashboard\EventEditorController;
use App\Http\Controllers\Dashboard\GuestController;
use App\Http\Controllers\Dashboard\EventTemplateController;
use App\Http\Controllers\Dashboard\DashboardWishController;
use App\Http\Controllers\Dashboard\RsvpDashboardController;
use App\Http\Controllers\Dashboard\BackupController;
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

// ─── Template preview (public, sample data) ───────────────────────────────────
Route::get('/template-preview/{template}', [TemplatePreviewController::class, 'show'])->name('template.preview');

// ─── Google OAuth ─────────────────────────────────────────────────────────────
Route::get('/auth/google',          [GoogleController::class, 'redirect'])->name('auth.google');
Route::get('/auth/google/callback', [GoogleController::class, 'callback'])->name('auth.google.callback');

// ─── Health check ─────────────────────────────────────────────────────────────
Route::get('/health', function () {
    $checks = [];
    try { \DB::select('SELECT 1'); $checks['db'] = 'ok'; } catch (\Throwable) { $checks['db'] = 'error'; }
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
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified', 'role:host|admin'])
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
    });

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
