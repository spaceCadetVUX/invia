<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMusicRequest;
use App\Http\Requests\UpdateMusicRequest;
use App\Models\MusicLibrary;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
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

    public function store(StoreMusicRequest $request): RedirectResponse
    {
        $file = $request->file('file');
        $path = Storage::disk()->putFile('music', $file);

        $coverPath = $request->hasFile('cover_image')
            ? Storage::disk()->putFile('music/covers', $request->file('cover_image'))
            : null;

        MusicLibrary::create([
            'title'       => $request->validated('title'),
            'artist'      => $request->validated('artist'),
            'mood'        => $request->validated('mood'),
            'file_path'   => $path,
            'cover_image' => $coverPath,
            'duration'    => $this->getAudioDuration($file->getRealPath()),
            'is_active'   => true,
        ]);

        return redirect()->route('admin.music.index')->with('success', 'Track uploaded successfully.');
    }

    public function update(UpdateMusicRequest $request, MusicLibrary $track): RedirectResponse
    {
        $data = $request->safe()->except('cover_image');

        if ($request->hasFile('cover_image')) {
            if ($track->cover_image) Storage::disk()->delete($track->cover_image);
            $data['cover_image'] = Storage::disk()->putFile('music/covers', $request->file('cover_image'));
        }

        $track->update($data);

        return redirect()->route('admin.music.index')->with('success', 'Track updated.');
    }

    public function cover(MusicLibrary $track): StreamedResponse
    {
        abort_unless($track->cover_image && Storage::disk()->exists($track->cover_image), 404);

        $ext  = strtolower(pathinfo($track->cover_image, PATHINFO_EXTENSION));
        $mime = match ($ext) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png'         => 'image/png',
            'webp'        => 'image/webp',
            default       => 'image/jpeg',
        };

        return Storage::disk()->response($track->cover_image, null, [
            'Content-Type'  => $mime,
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    public function stream(MusicLibrary $track): StreamedResponse
    {
        abort_unless(Storage::disk()->exists($track->file_path), 404);

        return Storage::disk()->response($track->file_path, null, [
            'Content-Type'  => 'audio/mpeg',
            'Accept-Ranges' => 'bytes',
        ]);
    }

    public function destroy(MusicLibrary $track): RedirectResponse
    {
        Storage::disk()->delete($track->file_path);
        $track->delete();

        return redirect()->route('admin.music.index')->with('success', 'Track deleted.');
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
