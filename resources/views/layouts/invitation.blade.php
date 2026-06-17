<!DOCTYPE html>
<html lang="{{ $event->language ?? 'vi' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $ogMeta['title'] }}</title>

    <meta property="og:type"          content="website">
    <meta property="og:title"         content="{{ $ogMeta['title'] }}">
    <meta property="og:description"   content="{{ $ogMeta['description'] }}">
    <meta property="og:url"           content="{{ $ogMeta['url'] }}">
    <meta property="og:image"         content="{{ $ogMeta['image'] }}">
    <meta property="og:image:width"   content="{{ $ogMeta['image_width'] }}">
    <meta property="og:image:height"  content="{{ $ogMeta['image_height'] }}">
    <meta property="og:locale"        content="vi_VN">

    <meta name="twitter:card"         content="summary_large_image">
    <meta name="twitter:title"        content="{{ $ogMeta['title'] }}">
    <meta name="twitter:description"  content="{{ $ogMeta['description'] }}">
    <meta name="twitter:image"        content="{{ $ogMeta['image'] }}">

    <meta name="robots" content="noindex, nofollow">

    @stack('styles')
</head>
<body>
    @yield('content')

    @include('partials.invitation-music', ['music' => $music])

    @include('partials.invitation-actions', ['event' => $event, 'guest' => $guest])

    @stack('scripts')
</body>
</html>
