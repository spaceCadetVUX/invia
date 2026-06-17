<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Template;
use Illuminate\Http\Request;

class ThiepController extends Controller
{
    public function show(string $slug, Request $request)
    {
        $event = Event::where('slug', $slug)
            ->with('template')
            ->firstOrFail();

        abort_if(!$event->template, 404);

        $allowedTemplates = Template::where('is_active', true)->pluck('blade_file')->toArray();
        abort_if(!in_array($event->template->blade_file, $allowedTemplates), 404);

        $guest = null;
        $token = $request->query('t');
        if ($token) {
            $guest = $event->guests()->where('token', $token)->first();
        }

        return view('thiep.show', compact('event', 'guest'));
    }
}
