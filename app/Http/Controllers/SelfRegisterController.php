<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Guest;
use App\Services\PlanService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SelfRegisterController extends Controller
{
    public function form(string $slug): View
    {
        $event = Event::where('slug', $slug)->where('status', 'published')->firstOrFail();
        abort_if(!$event->self_register_enabled, 404);

        return view('invitation.register', compact('event'));
    }

    public function store(Request $request, string $slug): RedirectResponse
    {
        $event = Event::where('slug', $slug)->where('status', 'published')->firstOrFail();
        abort_if(!$event->self_register_enabled, 403);

        $data = $request->validate([
            'name'  => ['required', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:150'],
            'phone' => ['nullable', 'string', 'max:20'],
        ]);

        RateLimiter::attempt(
            "self-register:{$request->ip()}",
            10,
            fn () => null,
        ) || abort(429);

        app(PlanService::class)->assertCanAddGuests($event);

        Guest::create([
            'event_id' => $event->id,
            'name'     => $data['name'],
            'email'    => $data['email'] ?? null,
            'phone'    => $data['phone'] ?? null,
            'token'    => Str::random(32),
            'source'   => 'self_register',
        ]);

        return redirect()->route('invitation.register.success', $slug);
    }

    public function success(string $slug): View
    {
        $event = Event::where('slug', $slug)->where('status', 'published')->firstOrFail();

        return view('invitation.register-success', compact('event'));
    }
}
