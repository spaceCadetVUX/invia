<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminUploadController extends Controller
{
    public function image(Request $request): JsonResponse
    {
        $request->validate([
            'image' => ['required', 'image', 'mimes:jpeg,png,webp,gif', 'max:4096'],
        ]);

        $path = $request->file('image')->store('blog/images', 'public');

        return response()->json([
            'url' => Storage::disk('public')->url($path),
        ]);
    }
}
