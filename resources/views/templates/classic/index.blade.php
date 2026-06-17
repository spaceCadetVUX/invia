@extends('layouts.invitation')

@section('content')
@php
    $settings  = $event->settings ?? [];
    $brideName = $settings['bride_name']['value'] ?? 'Cô Dâu';
    $groomName = $settings['groom_name']['value'] ?? 'Chú Rể';
@endphp

<div class="tmpl-classic" style="padding-bottom:4rem;">

    @if ($guest)
        <div class="guest-name-banner" style="
            text-align:center; padding:1rem 1.5rem;
            background:rgba(201,169,110,0.12); border-bottom:1px solid rgba(201,169,110,0.3);
            font-size:0.95rem; color:#7c6432;
        ">
            Kính mời <strong>{{ $guest->name }}</strong>
        </div>
    @endif

    <div class="hero">
        <div class="ornament">✦ ✦ ✦</div>
        <p style="letter-spacing:0.3rem; font-size:0.85rem; text-transform:uppercase; color:#c9a96e;">
            {{ __('thiep.invitation') }}
        </p>
    </div>

    <div class="bride-name">{{ $brideName }}</div>
    <div class="connector">&</div>
    <div class="groom-name">{{ $groomName }}</div>

    <div class="divider"></div>

    <div class="event-date">
        {{ $event->event_date?->format('d · m · Y') }}
    </div>

    @if ($event->event_time)
        <div class="event-time" style="text-align:center; color:#7c6432; font-size:0.9rem; margin-top:0.25rem;">
            {{ \Illuminate\Support\Str::substr((string) $event->event_time, 0, 5) }}
        </div>
    @endif

    <div class="event-venue">{{ $event->venue_name }}</div>

    @if ($event->venue_address)
        <div class="event-address" style="text-align:center; color:#9ca3af; font-size:0.85rem; margin-top:0.25rem;">
            {{ $event->venue_address }}
        </div>
    @endif

    @foreach ($settings['slots'] ?? [] as $slot)
        <div class="tmpl-slot" style="
            top: {{ $slot['top'] ?? 0 }}%;
            left: {{ $slot['left'] ?? 0 }}%;
            font-size: {{ $slot['fontSize'] ?? '16px' }};
            color: {{ $slot['color'] ?? 'inherit' }};
        ">{{ $slot['value'] }}</div>
    @endforeach

</div>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('templates/classic/style.css') }}?v={{ $event->template->version }}">
@endpush

@push('scripts')
    <script src="{{ asset('templates/classic/script.js') }}?v={{ $event->template->version }}" defer></script>
@endpush
