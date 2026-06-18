<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMusicRequest;
use App\Models\MusicLibrary;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class AdminMusicController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Music', [
            'tracks' => MusicLibrary::where('is_active', true)
                ->orderByDesc('created_at')
                ->get(),
        ]);
    }

    public function store(StoreMusicRequest $request): JsonResponse
    {
        $file = $request->file('file');
        $path = Storage::disk()->putFile('music', $file);

        $track = MusicLibrary::create([
            'title'     => $request->validated('title'),
            'artist'    => $request->validated('artist'),
            'mood'      => $request->validated('mood'),
            'file_path' => $path,
            'duration'  => $this->getAudioDuration($file->getRealPath()),
            'is_active' => true,
        ]);

        return response()->json($track, 201);
    }

    public function destroy(MusicLibrary $track): JsonResponse
    {
        Storage::disk()->delete($track->file_path);
        $track->delete();

        return response()->json(['deleted' => true]);
    }

    private function getAudioDuration(string $path): ?int
    {
        try {
            $getID3 = new \getID3();
            return (int) ($getID3->analyze($path)['playtime_seconds'] ?? null);
        } catch (\Throwable) {
            return null;
        }
    }
}
