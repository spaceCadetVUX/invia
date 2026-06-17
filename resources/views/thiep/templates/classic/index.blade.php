@section('template-styles')
    <link rel="stylesheet" href="{{ asset('templates/classic/style.css') }}?v={{ $event->template->version }}">
@endsection

<div class="tmpl-classic">
    <div class="hero">
        <div class="ornament">✦ ✦ ✦</div>
        <p style="letter-spacing:0.3rem; font-size:0.85rem; text-transform:uppercase; color:#c9a96e;">
            {{ __('thiep.invitation') }}
        </p>
    </div>

    @php
        $settings = $event->settings ?? [];
        $brideName = $settings['bride_name']['value'] ?? ($event->bride_name ?? 'Cô Dâu');
        $groomName = $settings['groom_name']['value'] ?? ($event->groom_name ?? 'Chú Rể');
    @endphp

    <div class="bride-name">{{ $brideName }}</div>
    <div class="connector">& </div>
    <div class="groom-name">{{ $groomName }}</div>

    <div class="divider"></div>

    <div class="event-date">
        {{ $event->date?->format('d · m · Y') }}
    </div>
    <div class="event-venue">{{ $event->venue }}</div>

    <div class="actions">
        <a href="#rsvp" class="btn-rsvp">{{ __('thiep.rsvp') }}</a>
        <a href="#wish" class="btn-wish">{{ __('thiep.send_wish') }}</a>
    </div>

    @if($event->music_source || $event->music_type === 'library')
        <button class="music-btn" id="musicToggle" title="Nhạc nền">♪</button>
    @endif
</div>

@section('template-scripts')
    <script src="{{ asset('templates/classic/script.js') }}?v={{ $event->template->version }}" defer></script>
@endsection
