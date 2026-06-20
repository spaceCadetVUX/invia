<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MusicCollection;
use App\Models\MusicLibrary;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminMusicCollectionController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'        => 'required|string|max:200',
            'description' => 'nullable|string|max:1000',
            'cover_image' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('cover_image')) {
            $data['cover_image'] = Storage::disk()->putFile('music/collection-covers', $request->file('cover_image'));
        }

        MusicCollection::create($data);

        return redirect()->route('admin.music.index')->with('success', 'Collection created.');
    }

    public function update(Request $request, MusicCollection $collection): RedirectResponse
    {
        $data = $request->validate([
            'name'        => 'required|string|max:200',
            'description' => 'nullable|string|max:1000',
            'cover_image' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('cover_image')) {
            if ($collection->cover_image) Storage::disk()->delete($collection->cover_image);
            $data['cover_image'] = Storage::disk()->putFile('music/collection-covers', $request->file('cover_image'));
        } else {
            unset($data['cover_image']);
        }

        $collection->update($data);

        return redirect()->route('admin.music.index')->with('success', 'Collection updated.');
    }

    public function destroy(MusicCollection $collection): RedirectResponse
    {
        if ($collection->cover_image) Storage::disk()->delete($collection->cover_image);
        $collection->delete();

        return redirect()->route('admin.music.index')->with('success', 'Collection deleted.');
    }

    public function cover(MusicCollection $collection): StreamedResponse
    {
        abort_unless($collection->cover_image && Storage::disk()->exists($collection->cover_image), 404);

        $ext  = strtolower(pathinfo($collection->cover_image, PATHINFO_EXTENSION));
        $mime = match ($ext) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png'         => 'image/png',
            'webp'        => 'image/webp',
            default       => 'image/jpeg',
        };

        return Storage::disk()->response($collection->cover_image, null, [
            'Content-Type'  => $mime,
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    public function addTracks(Request $request, MusicCollection $collection): RedirectResponse
    {
        $request->validate([
            'track_ids'   => 'required|array',
            'track_ids.*' => 'integer|exists:music_library,id',
        ]);

        $maxOrder = $collection->tracks()->max('music_collection_tracks.sort_order') ?? -1;

        $sync = [];
        foreach ($request->track_ids as $i => $trackId) {
            $sync[$trackId] = ['sort_order' => $maxOrder + 1 + $i];
        }

        $collection->tracks()->syncWithoutDetaching($sync);

        return redirect()->route('admin.music.index')->with('success', 'Tracks added.');
    }

    public function removeTrack(MusicCollection $collection, MusicLibrary $track): RedirectResponse
    {
        $collection->tracks()->detach($track->id);

        return redirect()->route('admin.music.index')->with('success', 'Track removed.');
    }

    public function reorder(Request $request, MusicCollection $collection): RedirectResponse
    {
        $request->validate([
            'order'   => 'required|array',
            'order.*' => 'integer|exists:music_library,id',
        ]);

        foreach ($request->order as $position => $trackId) {
            $collection->tracks()->updateExistingPivot($trackId, ['sort_order' => $position]);
        }

        return redirect()->route('admin.music.index');
    }
}
