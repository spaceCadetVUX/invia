<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class AdminDashboardController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'users'         => User::count(),
                'events_total'  => Event::count(),
                'events_active' => Event::where(fn ($q) =>
                    $q->whereNull('expires_at')->orWhere('expires_at', '>', now())
                )->count(),
                'revenue_today' => (int) Payment::where('status', 'paid')
                    ->whereDate('paid_at', today())->sum('amount'),
                'revenue_month' => (int) Payment::where('status', 'paid')
                    ->whereMonth('paid_at', now()->month)
                    ->whereYear('paid_at', now()->year)
                    ->sum('amount'),
                'queue_size'    => config('queue.default') === 'database'
                    ? DB::table('jobs')->count()
                    : null,
            ],
            'recent_payments' => Payment::where('status', 'paid')
                ->with(['user:id,name,email', 'event:id,slug,title'])
                ->latest('paid_at')
                ->take(10)
                ->get(['id', 'user_id', 'event_id', 'type', 'plan', 'amount', 'paid_at']),
        ]);
    }
}
