@extends('layouts.blog')

@section('title', 'Blog')
@section('description', 'Mẹo hay về thiệp mời đám cưới, sinh nhật và sự kiện từ Invia.vn')

@section('og-tags')
    <meta property="og:site_name"   content="Invia.vn">
    <meta property="og:locale"      content="vi_VN">
    <meta property="og:type"        content="website">
    <meta property="og:title"       content="Blog — Invia.vn">
    <meta property="og:description" content="Mẹo hay về thiệp mời đám cưới, sinh nhật và sự kiện">
    <meta property="og:url"         content="{{ route('blog.index') }}">
    <meta name="twitter:card"       content="summary_large_image">
    <meta name="twitter:site"       content="@inviaVN">
@endsection

@section('content')
<main class="max-w-5xl mx-auto px-4 sm:px-6 py-14">

    {{-- Header --}}
    <div class="mb-12 text-center">
        <h1 class="text-4xl font-bold text-[#1E1E2D] tracking-tight">Blog</h1>
        <p class="mt-3 text-gray-500 text-lg">Chia sẻ về thiệp mời, đám cưới và những khoảnh khắc đáng nhớ</p>
    </div>

    @if($posts->isEmpty())
        <div class="text-center py-24 text-gray-400">
            <svg class="w-12 h-12 mx-auto mb-4 text-gray-200" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/>
            </svg>
            <p class="text-base">Chưa có bài viết nào.</p>
        </div>
    @else
        {{-- Post grid --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($posts as $post)
                <article class="group flex flex-col rounded-2xl overflow-hidden border border-gray-100 hover:shadow-lg transition-shadow duration-200 bg-white">

                    {{-- Cover --}}
                    <a href="{{ route('blog.show', $post->slug) }}" class="block aspect-[16/9] overflow-hidden bg-gray-100">
                        @if($post->cover_image_path)
                            <img src="{{ Storage::url($post->cover_image_path) }}"
                                 alt="{{ $post->title }}"
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                        @else
                            <div class="w-full h-full bg-gradient-to-br from-[#EEF4FB] to-[#dbeafe] flex items-center justify-center">
                                <svg class="w-10 h-10 text-[#5B9FD6]/40" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.645 20.91l-.007-.003-.022-.012a15.247 15.247 0 01-.383-.218 25.18 25.18 0 01-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3A5.5 5.5 0 0112 5.052 5.5 5.5 0 0116.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 01-4.244 3.17 15.247 15.247 0 01-.383.219l-.022.012-.007.004-.003.001a.752.752 0 01-.704 0l-.003-.001z"/>
                                </svg>
                            </div>
                        @endif
                    </a>

                    {{-- Body --}}
                    <div class="flex flex-col flex-1 p-5">
                        {{-- Meta --}}
                        <div class="flex items-center gap-2 text-xs text-gray-400 mb-2.5">
                            <time datetime="{{ $post->published_at->toDateString() }}">
                                {{ $post->published_at->translatedFormat('d M, Y') }}
                            </time>
                            <span>·</span>
                            <span>{{ $post->reading_time }} phút đọc</span>
                        </div>

                        {{-- Title --}}
                        <a href="{{ route('blog.show', $post->slug) }}"
                           class="font-semibold text-[#1E1E2D] text-base leading-snug group-hover:text-[#2563eb] transition-colors line-clamp-2 mb-2">
                            {{ $post->title }}
                        </a>

                        {{-- Excerpt --}}
                        @if($post->excerpt)
                            <p class="text-sm text-gray-500 line-clamp-3 flex-1">{{ $post->excerpt }}</p>
                        @endif

                        {{-- Author + read more --}}
                        <div class="flex items-center justify-between mt-4 pt-4 border-t border-gray-50">
                            <span class="text-xs text-gray-400">{{ $post->author?->name ?? 'Invia' }}</span>
                            <a href="{{ route('blog.show', $post->slug) }}"
                               class="text-xs font-medium text-[#2563eb] hover:underline flex items-center gap-1">
                                Đọc tiếp
                                <svg class="w-3.5 h-3.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        {{-- Pagination --}}
        @if($posts->hasPages())
            <div class="mt-12 flex justify-center">
                {{ $posts->links() }}
            </div>
        @endif
    @endif

</main>
@endsection
