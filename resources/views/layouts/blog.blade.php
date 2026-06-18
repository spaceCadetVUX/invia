<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'Blog') — Invia.vn</title>
    <meta name="description" content="@yield('description', 'Blog về thiệp mời đám cưới, sinh nhật và sự kiện từ Invia.vn')">

    @yield('og-tags')

    @hasSection('canonical')
        <link rel="canonical" href="@yield('canonical')">
    @endif

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css'])

    <style>
        .prose { color: #1f2937; line-height: 1.8; }
        .prose h1 { font-size: 2rem; font-weight: 700; margin: 2rem 0 .75rem; line-height: 1.2; color: #111827; }
        .prose h2 { font-size: 1.5rem; font-weight: 600; margin: 2rem 0 .5rem; line-height: 1.3; color: #111827; border-bottom: 1px solid #f3f4f6; padding-bottom: .375rem; }
        .prose h3 { font-size: 1.25rem; font-weight: 600; margin: 1.5rem 0 .4rem; color: #111827; }
        .prose h4 { font-size: 1.125rem; font-weight: 600; margin: 1.25rem 0 .35rem; color: #374151; }
        .prose p  { margin: 1rem 0; }
        .prose a  { color: #2563eb; text-decoration: underline; text-underline-offset: 2px; }
        .prose a:hover { color: #1d4ed8; }
        .prose strong { font-weight: 600; color: #111827; }
        .prose em { font-style: italic; }
        .prose ul { list-style: disc; padding-left: 1.5rem; margin: 1rem 0; }
        .prose ol { list-style: decimal; padding-left: 1.5rem; margin: 1rem 0; }
        .prose li { margin: .375rem 0; }
        .prose li > p { margin: .375rem 0; }
        .prose blockquote { border-left: 3px solid #e5e7eb; padding-left: 1rem; color: #6b7280; margin: 1.25rem 0; font-style: italic; }
        .prose code { background: #f3f4f6; padding: .15rem .4rem; border-radius: 4px; font-size: .875em; font-family: 'Fira Code', 'Consolas', monospace; color: #be123c; }
        .prose pre  { background: #1e293b; color: #e2e8f0; padding: 1.25rem; border-radius: 10px; overflow-x: auto; margin: 1.5rem 0; }
        .prose pre code { background: transparent; padding: 0; color: inherit; font-size: .875rem; }
        .prose img  { max-width: 100%; height: auto; border-radius: 10px; margin: 1.5rem 0; }
        .prose hr   { border: 0; border-top: 1px solid #e5e7eb; margin: 2rem 0; }
        .prose table { width: 100%; border-collapse: collapse; margin: 1.25rem 0; font-size: .9rem; }
        .prose th, .prose td { padding: .5rem .75rem; border: 1px solid #e5e7eb; text-align: left; }
        .prose th { background: #f9fafb; font-weight: 600; }
    </style>
</head>
<body class="bg-white font-sans antialiased text-gray-900">

    {{-- Nav --}}
    <header class="border-b border-gray-100 sticky top-0 bg-white/95 backdrop-blur z-20">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 h-14 flex items-center justify-between">
            <a href="{{ url('/') }}" class="flex items-center gap-2 group">
                <div class="w-7 h-7 bg-[#1E1E2D] rounded-lg flex items-center justify-center group-hover:bg-[#2a2a3d] transition-colors">
                    <svg viewBox="0 0 24 24" fill="white" class="w-4 h-4">
                        <path d="M11.645 20.91l-.007-.003-.022-.012a15.247 15.247 0 01-.383-.218 25.18 25.18 0 01-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3A5.5 5.5 0 0112 5.052 5.5 5.5 0 0116.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 01-4.244 3.17 15.247 15.247 0 01-.383.219l-.022.012-.007.004-.003.001a.752.752 0 01-.704 0l-.003-.001z"/>
                    </svg>
                </div>
                <span class="font-semibold text-[#1E1E2D] text-sm">Invia.vn</span>
            </a>

            <nav class="flex items-center gap-6 text-sm">
                <a href="{{ route('blog.index') }}"
                   class="font-medium {{ request()->routeIs('blog.*') ? 'text-[#1E1E2D]' : 'text-gray-500 hover:text-gray-900' }} transition-colors">
                    Blog
                </a>
                <a href="{{ url('/') }}"
                   class="text-gray-500 hover:text-gray-900 transition-colors">
                    Trang chủ
                </a>
                @auth
                    <a href="{{ route('dashboard') }}"
                       class="px-3.5 py-1.5 bg-[#1E1E2D] text-white rounded-lg text-xs font-medium hover:bg-[#2a2a3d] transition-colors">
                        Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}"
                       class="px-3.5 py-1.5 bg-[#1E1E2D] text-white rounded-lg text-xs font-medium hover:bg-[#2a2a3d] transition-colors">
                        Đăng nhập
                    </a>
                @endauth
            </nav>
        </div>
    </header>

    {{-- Content --}}
    @yield('content')

    {{-- Footer --}}
    <footer class="mt-24 border-t border-gray-100 py-10">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 flex flex-col sm:flex-row items-center justify-between gap-4 text-sm text-gray-400">
            <p>© {{ date('Y') }} Invia.vn — Thiệp mời online</p>
            <div class="flex gap-5">
                <a href="{{ route('blog.index') }}" class="hover:text-gray-600 transition-colors">Blog</a>
                <a href="{{ url('/') }}" class="hover:text-gray-600 transition-colors">Trang chủ</a>
            </div>
        </div>
    </footer>

</body>
</html>
