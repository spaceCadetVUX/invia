@extends('layouts.thiep')

@section('og-tags')
    <meta property="og:title"       content="{{ $event->title }}" />
    <meta property="og:description" content="{{ $event->date?->format('d/m/Y') }} • {{ $event->venue }}" />
    <meta property="og:image"       content="{{ $event->og_image_path ? Storage::url($event->og_image_path) : asset('img/og-default.jpg') }}" />
    <meta property="og:url"         content="{{ url('/thiep/' . $event->slug) }}" />
    <meta property="og:type"        content="website" />
    <meta name="twitter:card"       content="summary_large_image" />
@endsection

@section('title', $event->title)

@include('thiep.templates.' . $event->template->blade_file . '.index')
