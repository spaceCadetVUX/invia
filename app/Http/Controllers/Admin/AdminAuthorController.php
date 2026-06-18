<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class AdminAuthorController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Authors', [
            'authors' => User::role(['admin', 'host'])
                ->withCount('blogPosts')
                ->orderBy('name')
                ->get(['id', 'name', 'email', 'avatar_url', 'bio', 'job_title',
                       'website', 'twitter_url', 'linkedin_url', 'facebook_url', 'created_at']),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'  => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
        ]);

        $user = User::create([
            'name'              => $data['name'],
            'email'             => $data['email'],
            'password'          => Hash::make(Str::random(24)),
            'email_verified_at' => now(),
        ]);

        $user->assignRole('host');

        return response()->json([
            'id'               => $user->id,
            'name'             => $user->name,
            'email'            => $user->email,
            'avatar_url'       => null,
            'bio'              => null,
            'job_title'        => null,
            'website'          => null,
            'twitter_url'      => null,
            'linkedin_url'     => null,
            'facebook_url'     => null,
            'blog_posts_count' => 0,
            'created_at'       => $user->created_at,
        ], 201);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $data = $request->validate([
            'name'         => ['required', 'string', 'max:100'],
            'bio'          => ['nullable', 'string', 'max:1000'],
            'job_title'    => ['nullable', 'string', 'max:100'],
            'website'      => ['nullable', 'url', 'max:255'],
            'twitter_url'  => ['nullable', 'url', 'max:255'],
            'linkedin_url' => ['nullable', 'url', 'max:255'],
            'facebook_url' => ['nullable', 'url', 'max:255'],
        ]);

        $user->update($data);

        return response()->json($user->only([
            'id', 'name', 'bio', 'job_title',
            'website', 'twitter_url', 'linkedin_url', 'facebook_url',
        ]));
    }

    public function destroy(User $user): JsonResponse
    {
        $user->removeRole('host');
        $user->removeRole('admin');

        return response()->json(['removed' => true]);
    }
}
