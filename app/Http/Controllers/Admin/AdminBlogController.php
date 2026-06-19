<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBlogPostRequest;
use App\Models\BlogPost;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class AdminBlogController extends Controller
{
    public function index(Request $request): Response
    {
        $search = trim($request->get('search', ''));
        $status = $request->get('status'); // 'published' | 'draft'

        $query = BlogPost::with('author:id,name')
            ->when($status === 'published', fn ($q) => $q->where('is_published', true))
            ->when($status === 'draft',     fn ($q) => $q->where('is_published', false));

        if ($search) {
            $vector = "to_tsvector('simple', unaccent(coalesce(title,'') || ' ' || coalesce(excerpt,'')))";
            $tsq    = "websearch_to_tsquery('simple', unaccent(?))";

            $query->whereRaw("{$vector} @@ {$tsq}", [$search])
                  ->orderByRaw("ts_rank({$vector}, {$tsq}) DESC", [$search]);
        } else {
            $query->orderByDesc('created_at');
        }

        return Inertia::render('Admin/Blog', [
            'posts'   => $query->paginate(20)->withQueryString(),
            'filters' => ['search' => $search ?: null, 'status' => $status],
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
