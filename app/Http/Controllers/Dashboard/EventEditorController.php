<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveSettingsRequest;
use App\Models\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;
use Inertia\Inertia;
use Inertia\Response;

class EventEditorController extends Controller
{
    public function show(Event $event): Response
    {
        $this->authorize('update', $event);

        $configPath = public_path("templates/{$event->template->blade_file}/config.json");
        abort_unless(file_exists($configPath), 500, 'Template config.json missing');

        $templateConfig = json_decode(file_get_contents($configPath), true);

        return Inertia::render('Dashboard/Events/Editor', [
            'event'          => $event->only('id', 'slug', 'title', 'settings'),
            'templateConfig' => $templateConfig,
            'templateMeta'   => $event->template->only('blade_file', 'version'),
        ]);
    }

    public function saveSettings(SaveSettingsRequest $request, Event $event): JsonResponse
    {
        $this->authorize('update', $event);

        $event->update(['settings' => $request->validated('settings')]);

        Cache::forget("invitation:{$event->slug}");

        return response()->json(['saved_at' => now()->toISOString()]);
    }

    public function preview(Event $event): View
    {
        $this->authorize('update', $event);

        $event->loadMissing('template');

        return view("templates.{$event->template->blade_file}.index", [
            'event'     => $event,
            'guest'     => null,
            'music'     => ['type' => 'none'],
            'ogMeta'    => [],
            'isPreview' => true,
        ]);
    }
}
