<?php

use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\Dashboard\EventController;
use App\Http\Controllers\Dashboard\EventTemplateController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TemplatePreviewController;
use App\Http\Controllers\ThiepController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// ─── Public: trang thiệp (Blade, server-side render) ─────────────────────────
Route::get('/thiep/{slug}', [ThiepController::class, 'show'])->name('thiep.show');

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
});

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
    });

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
