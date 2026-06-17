<?php

namespace App\Http\Controllers;

use App\Models\Guest;
use Illuminate\View\View;

class UnsubscribeController extends Controller
{
    public function handle(string $token): View
    {
        $guest = Guest::where('token', $token)->firstOrFail();

        if (!$guest->unsubscribed_at) {
            $guest->update(['unsubscribed_at' => now()]);
        }

        return view('invitation.unsubscribed', ['guest' => $guest]);
    }
}
