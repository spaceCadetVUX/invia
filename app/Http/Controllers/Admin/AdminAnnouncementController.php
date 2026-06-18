<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemAnnouncement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class AdminAnnouncementController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Announcements', [
            'announcements' => SystemAnnouncement::orderByDesc('created_at')->get(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'title'     => ['required', 'string', 'max:200'],
            'body'      => ['nullable', 'string', 'max:1000'],
            'type'      => ['required', Rule::in(['info', 'warning', 'error'])],
            'starts_at' => ['nullable', 'date'],
            'ends_at'   => ['nullable', 'date', 'after:starts_at'],
            'is_active' => ['boolean'],
        ]);

        $ann = SystemAnnouncement::create($data);

        return response()->json($ann, 201);
    }

    public function update(Request $request, SystemAnnouncement $ann): JsonResponse
    {
        $data = $request->validate([
            'title'     => ['sometimes', 'string', 'max:200'],
            'body'      => ['nullable', 'string', 'max:1000'],
            'type'      => ['sometimes', Rule::in(['info', 'warning', 'error'])],
            'starts_at' => ['nullable', 'date'],
            'ends_at'   => ['nullable', 'date'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $ann->update($data);

        return response()->json($ann->fresh());
    }

    public function destroy(SystemAnnouncement $ann): JsonResponse
    {
        $ann->delete();

        return response()->json(['deleted' => true]);
    }
}
