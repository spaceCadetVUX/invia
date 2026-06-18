<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class AdminEventController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Event::with(['user:id,name,email', 'template:id,name'])
            ->orderByDesc('created_at');

        if ($search = $request->query('search')) {
            $query->where(fn ($q) =>
                $q->where('title', 'ilike', "%{$search}%")
                  ->orWhere('slug', 'ilike', "%{$search}%")
                  ->orWhereHas('user', fn ($u) =>
                      $u->where('email', 'ilike', "%{$search}%")
                  )
            );
        }

        return Inertia::render('Admin/Events', [
            'events'  => $query->paginate(50)->withQueryString(),
            'filters' => $request->only(['search']),
        ]);
    }

    public function destroy(Event $event): JsonResponse
    {
        if ($event->og_image_path) {
            Storage::disk()->delete($event->og_image_path);
        }

        $settings = $event->settings ?? [];
        foreach ($settings as $slot) {
            if (!empty($slot['file_path'])) {
                Storage::disk()->delete($slot['file_path']);
            }
        }

        Log::warning("Admin force-deleted event {$event->id} ({$event->slug}), title: {$event->title}, owner: {$event->user_id}. By admin: " . auth()->id());

        $event->delete();

        return response()->json(['deleted' => true]);
    }
}
