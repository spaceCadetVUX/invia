<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBlogPostRequest;
use App\Models\BlogPost;
use App\Models\User;
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

    public function create(): Response
    {
        return Inertia::render('Admin/BlogCreate', [
            'authors' => User::role(['admin', 'host'])->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function edit(BlogPost $post): Response
    {
        return Inertia::render('Admin/BlogEdit', [
            'post'    => $post->load('author:id,name'),
            'authors' => User::role(['admin', 'host'])->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(StoreBlogPostRequest $request)
    {
        $data              = $request->validated();
        $data['slug']      = Str::slug($data['title']) . '-' . Str::random(6);
        $data['author_id'] = $data['author_id'] ?? auth()->id();

        if (!empty($data['is_published']) && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        if ($request->hasFile('cover_image')) {
            $data['cover_image_path'] = $request->file('cover_image')
                ->store('blog/covers', 'public');
        }

        unset($data['cover_image']);

        BlogPost::create($data);

        return redirect()->route('admin.blog.index');
    }

    public function update(StoreBlogPostRequest $request, BlogPost $post)
    {
        $data = $request->validated();

        if (!empty($data['is_published']) && !$post->published_at) {
            $data['published_at'] = now();
        }

        if ($request->hasFile('cover_image')) {
            if ($post->cover_image_path) {
                Storage::disk('public')->delete($post->cover_image_path);
            }
            $data['cover_image_path'] = $request->file('cover_image')
                ->store('blog/covers', 'public');
        }

        unset($data['cover_image']);

        $post->update($data);

        return redirect()->route('admin.blog.index');
    }

    public function destroy(BlogPost $post)
    {
        if ($post->cover_image_path) {
            Storage::disk('public')->delete($post->cover_image_path);
        }

        $post->delete();

        return redirect()->route('admin.blog.index');
    }
}
