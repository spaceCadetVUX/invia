<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;

class BlogController extends Controller
{
    public function index()
    {
        $posts = BlogPost::with('author:id,name')
            ->published()
            ->orderByDesc('published_at')
            ->paginate(9);

        return view('blog.index', compact('posts'));
    }

    public function show(string $slug)
    {
        $post = BlogPost::with('author:id,name,bio,job_title,website,avatar_url,twitter_url,linkedin_url,facebook_url')
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();

        return view('blog.show', compact('post'));
    }
}
