<div class="invitation-actions" style="
    position:fixed; bottom:0; left:0; right:0; z-index:50;
    display:flex; justify-content:center; gap:0.75rem; padding:0.75rem 1rem;
    background:rgba(255,255,255,0.95); backdrop-filter:blur(8px);
    border-top:1px solid rgba(0,0,0,0.08); box-shadow:0 -2px 12px rgba(0,0,0,0.06);
">
    @if ($event->rsvp_enabled)
        <a href="{{ route('invitation.rsvp.form', $event->slug) }}{{ $guest ? '?t=' . $guest->token : '' }}"
           style="
               display:inline-flex; align-items:center; padding:0.5rem 1.25rem;
               background:#4f46e5; color:#fff; border-radius:0.5rem;
               text-decoration:none; font-size:0.875rem; font-weight:500;
           ">
            {{ __('invitation.rsvp_button') }}
        </a>
    @endif

    <a href="{{ route('invitation.calendar', $event->slug) }}"
       style="
           display:inline-flex; align-items:center; padding:0.5rem 1.25rem;
           background:#fff; color:#374151; border:1px solid #d1d5db; border-radius:0.5rem;
           text-decoration:none; font-size:0.875rem; font-weight:500;
       ">
        {{ __('invitation.add_to_calendar') }}
    </a>

    @if ($event->livestream_url)
        <a href="{{ $event->livestream_url }}" target="_blank" rel="noopener"
           style="
               display:inline-flex; align-items:center; padding:0.5rem 1.25rem;
               background:#dc2626; color:#fff; border-radius:0.5rem;
               text-decoration:none; font-size:0.875rem; font-weight:500;
           ">
            {{ __('invitation.watch_livestream') }}
        </a>
    @endif
</div>
