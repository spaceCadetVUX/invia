<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBlogPostRequest;
use App\Models\BlogPost;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class AdminBlogController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Blog', [
            'posts' => BlogPost::with('author:id,name')
                ->orderByDesc('created_at')
                ->paginate(20),
        ]);
    }

    public function store(StoreBlogPostRequest $request): JsonResponse
    {
        $data             = $request->validated();
        $data['slug']     = Str::slug($data['title']) . '-' . Str::random(6);
        $data['author_id'] = auth()->id();

        if (!empty($data['is_published']) && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        $post = BlogPost::create($data);

        return response()->json($post, 201);
    }

    public function update(StoreBlogPostRequest $request, BlogPost $post): JsonResponse
    {
        $data = $request->validated();

        if (!empty($data['is_published']) && !$post->published_at) {
            $data['published_at'] = now();
        }

        $post->update($data);

        return response()->json($post->fresh());
    }

    public function destroy(BlogPost $post): JsonResponse
    {
        if ($post->cover_image_path) {
            Storage::disk()->delete($post->cover_image_path);
        }

        $post->delete();

        return response()->json(['deleted' => true]);
    }
}
