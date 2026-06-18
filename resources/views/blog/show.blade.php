@php
    $seoTitle       = $post->meta_title ?: $post->title;
    $seoDescription = $post->meta_description ?: ($post->excerpt ?: Str::limit(strip_tags($post->content), 160));
    $seoImage       = $post->cover_image_path ? url(Storage::url($post->cover_image_path)) : null;

    $author = $post->author;
    $authorLd = ['@type' => 'Person', 'name' => $author?->name ?? 'Invia'];
    if ($author?->bio)          $authorLd['description'] = $author->bio;
    if ($author?->avatar_url)   $authorLd['image']       = $author->avatar_url;
    if ($author?->website)      $authorLd['url']         = $author->website;
    if ($author?->job_title)    $authorLd['jobTitle']    = $author->job_title;
    $sameAs = array_values(array_filter([
        $author?->twitter_url,
        $author?->linkedin_url,
        $author?->facebook_url,
    ]));
    if ($sameAs) $authorLd['sameAs'] = $sameAs;

    $articleLd = [
        '@context'      => 'https://schema.org',
        '@type'         => 'Article',
        'headline'      => $post->title,
        'description'   => $seoDescription,
        'url'           => route('blog.show', $post->slug),
        'datePublished' => $post->published_at->toIso8601String(),
        'dateModified'  => $post->updated_at->toIso8601String(),
        'author'        => $authorLd,
        'publisher'     => ['@type' => 'Organization', 'name' => 'Invia.vn', 'url' => url('/')],
    ];
    if ($seoImage) $articleLd['image'] = $seoImage;

    $faqLd = null;
    if (!empty($post->faq)) {
        $faqLd = [
            '@context'   => 'https://schema.org',
            '@type'      => 'FAQPage',
            'mainEntity' => collect($post->faq)->map(fn($f) => [
                '@type'          => 'Question',
                'name'           => $f['question'],
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f['answer']],
            ])->values()->all(),
        ];
    }
@endphp

@extends('layouts.blog')

@section('title', $seoTitle)
@section('description', $seoDescription)
@section('canonical', route('blog.show', $post->slug))

@section('og-tags')
    <meta property="og:site_name"   content="Invia.vn">
    <meta property="og:locale"      content="vi_VN">
    <meta property="og:type"        content="article">
    <meta property="og:title"       content="{{ $seoTitle }}">
    <meta property="og:description" content="{{ $seoDescription }}">
    <meta property="og:url"         content="{{ route('blog.show', $post->slug) }}">
    @if($seoImage)
        <meta property="og:image"         content="{{ $seoImage }}">
        <meta property="og:image:width"   content="1200">
        <meta property="og:image:height"  content="630">
        <meta property="og:image:alt"     content="{{ $seoTitle }}">
        <meta name="twitter:image"        content="{{ $seoImage }}">
    @endif
    <meta property="article:published_time" content="{{ $post->published_at->toIso8601String() }}">
    <meta property="article:modified_time"  content="{{ $post->updated_at->toIso8601String() }}">
    <meta property="article:author"         content="{{ $post->author?->name ?? 'Invia' }}">
    <meta name="twitter:card"               content="summary_large_image">
    <meta name="twitter:site"               content="@inviaVN">
    <meta name="twitter:title"              content="{{ $seoTitle }}">
    <meta name="twitter:description"        content="{{ $seoDescription }}">

    {{-- JSON-LD: Article --}}
    <script type="application/ld+json">
    {!! json_encode($articleLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
    </script>

    {{-- JSON-LD: FAQPage (nếu có FAQ) --}}
    @if($faqLd)
    <script type="application/ld+json">
    {!! json_encode($faqLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
    </script>
    @endif
@endsection

@section('content')
<main>
    {{-- Cover image --}}
    @if($seoImage)
        <div class="w-full max-h-[480px] overflow-hidden bg-gray-100">
            <img src="{{ $seoImage }}" alt="{{ $post->title }}" class="w-full h-full object-cover">
        </div>
    @endif

    <div class="max-w-2xl mx-auto px-4 sm:px-6 py-12">

        {{-- Back --}}
        <a href="{{ route('blog.index') }}"
           class="inline-flex items-center gap-1.5 text-sm text-gray-400 hover:text-gray-700 transition-colors mb-8">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m15 18-6-6 6-6"/></svg>
            Tất cả bài viết
        </a>

        {{-- Header --}}
        <header class="mb-10">
            <h1 class="text-3xl sm:text-4xl font-bold text-[#1E1E2D] leading-tight tracking-tight mb-5">
                {{ $post->title }}
            </h1>

            <div class="flex flex-wrap items-center gap-3 text-sm text-gray-400">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-full bg-[#EEF4FB] flex items-center justify-center text-[#5B9FD6] text-xs font-bold">
                        {{ strtoupper(substr($post->author?->name ?? 'I', 0, 1)) }}
                    </div>
                    <span class="font-medium text-gray-600">{{ $post->author?->name ?? 'Invia' }}</span>
                </div>
                <span>·</span>
                <time datetime="{{ $post->published_at->toDateString() }}">
                    {{ $post->published_at->translatedFormat('d M, Y') }}
                </time>
                <span>·</span>
                <span>{{ $post->reading_time }} phút đọc</span>
            </div>

            @if($post->excerpt)
                <p class="mt-6 text-lg text-gray-500 leading-relaxed border-l-4 border-[#EEF4FB] pl-4">
                    {{ $post->excerpt }}
                </p>
            @endif
        </header>

        <hr class="border-gray-100 mb-10">

        {{-- Content --}}
        <article class="prose max-w-none">
            {!! $post->content_html !!}
        </article>

        {{-- FAQ Section --}}
        @if(!empty($post->faq))
            <section class="mt-14" aria-label="Câu hỏi thường gặp">
                <h2 class="text-xl font-bold text-[#1E1E2D] mb-6 flex items-center gap-2">
                    <svg class="w-5 h-5 text-[#5B9FD6]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" stroke-linejoin="round" d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3M12 17h.01"/></svg>
                    Câu hỏi thường gặp
                </h2>

                <div class="space-y-3">
                    @foreach($post->faq as $i => $item)
                        <details class="group border border-gray-100 rounded-xl overflow-hidden" {{ $i === 0 ? 'open' : '' }}>
                            <summary class="flex items-center justify-between px-5 py-4 cursor-pointer select-none list-none font-medium text-[#1E1E2D] hover:bg-gray-50 transition-colors">
                                {{ $item['question'] }}
                                <svg class="w-4 h-4 text-gray-400 shrink-0 ml-3 transition-transform group-open:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/></svg>
                            </summary>
                            <div class="px-5 pb-5 pt-1 text-gray-600 text-sm leading-relaxed border-t border-gray-50">
                                {{ $item['answer'] }}
                            </div>
                        </details>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Footer CTA --}}
        <div class="mt-16 pt-8 border-t border-gray-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <a href="{{ route('blog.index') }}"
               class="inline-flex items-center gap-1.5 text-sm text-gray-400 hover:text-gray-700 transition-colors">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m15 18-6-6 6-6"/></svg>
                Xem tất cả bài viết
            </a>
            <a href="{{ route('home') }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-[#1E1E2D] text-white text-sm font-medium hover:bg-[#2a2a3d] transition-colors">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M11.645 20.91l-.007-.003-.022-.012a15.247 15.247 0 01-.383-.218 25.18 25.18 0 01-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3A5.5 5.5 0 0112 5.052 5.5 5.5 0 0116.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 01-4.244 3.17 15.247 15.247 0 01-.383.219l-.022.012-.007.004-.003.001a.752.752 0 01-.704 0l-.003-.001z"/></svg>
                Tạo thiệp mời miễn phí
            </a>
        </div>

    </div>
</main>
@endsection
