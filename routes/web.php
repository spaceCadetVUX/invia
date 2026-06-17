<?php

use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ThiepController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// ─── Public: trang thiệp (Blade, server-side render) ─────────────────────────
Route::get('/thiep/{slug}', [ThiepController::class, 'show'])->name('thiep.show');

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

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
