@extends('layouts.minimal')

@section('title', __('invitation.rsvp_button') . ' — ' . $event->title)

@section('content')
<div style="
    min-height:100vh; display:flex; align-items:flex-start; justify-content:center;
    padding:2rem 1rem; background:#f9fafb;
">
<div style="width:100%; max-width:480px;">

    <a href="{{ route('invitation.show', $event->slug) }}"
       style="display:inline-block; margin-bottom:1.5rem; font-size:0.875rem; color:#6b7280; text-decoration:none;">
        ← {{ $event->title }}
    </a>

    <h1 style="font-size:1.5rem; font-weight:700; margin-bottom:0.25rem; color:#111827;">
        {{ __('invitation.rsvp_button') }}
    </h1>
    <p style="color:#6b7280; font-size:0.875rem; margin-bottom:1.5rem;">
        {{ $event->venue_name }} — {{ $event->event_date->format('d/m/Y') }}
    </p>

    @if ($existingRsvp)
        <div style="
            background:#fef3c7; border:1px solid #fcd34d; border-radius:0.5rem;
            padding:0.75rem 1rem; margin-bottom:1.5rem; font-size:0.875rem; color:#92400e;
        ">
            {{ __('rsvp.already_submitted') }}
        </div>
    @endif

    @if ($errors->any())
        <div style="
            background:#fef2f2; border:1px solid #fca5a5; border-radius:0.5rem;
            padding:0.75rem 1rem; margin-bottom:1.5rem; font-size:0.875rem; color:#991b1b;
        ">
            <ul style="margin:0; padding-left:1.25rem;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST"
          action="{{ route('invitation.rsvp.store', $event->slug) }}{{ request('t') ? '?t=' . request('t') : '' }}"
          style="background:#fff; border-radius:0.75rem; padding:1.5rem; box-shadow:0 1px 4px rgba(0,0,0,0.08);">
        @csrf

        @if ($guest)
            <p style="margin-bottom:1.25rem; font-size:0.95rem; color:#374151;">
                {{ __('rsvp.greeting', ['name' => $guest->name]) }}
            </p>
            <input type="hidden" name="name" value="{{ $guest->name }}">
        @else
            <div style="margin-bottom:1rem;">
                <label style="display:block; font-size:0.875rem; font-weight:500; color:#374151; margin-bottom:0.375rem;">
                    {{ __('rsvp.your_name') }} <span style="color:#ef4444;">*</span>
                </label>
                <input type="text" name="name" value="{{ old('name') }}" required maxlength="100"
                       style="width:100%; padding:0.5rem 0.75rem; border:1px solid #d1d5db; border-radius:0.5rem; font-size:0.875rem; outline:none; box-sizing:border-box;">
                @error('name')
                    <span style="color:#ef4444; font-size:0.75rem;">{{ $message }}</span>
                @enderror
            </div>

            <div style="margin-bottom:1rem;">
                <label style="display:block; font-size:0.875rem; font-weight:500; color:#374151; margin-bottom:0.375rem;">
                    {{ __('rsvp.your_email') }}
                </label>
                <input type="email" name="email" value="{{ old('email') }}" maxlength="150"
                       style="width:100%; padding:0.5rem 0.75rem; border:1px solid #d1d5db; border-radius:0.5rem; font-size:0.875rem; outline:none; box-sizing:border-box;">
                @error('email')
                    <span style="color:#ef4444; font-size:0.75rem;">{{ $message }}</span>
                @enderror
            </div>
        @endif

        <fieldset style="border:1px solid #e5e7eb; border-radius:0.5rem; padding:1rem; margin-bottom:1rem;">
            <legend style="font-size:0.875rem; font-weight:500; color:#374151; padding:0 0.5rem;">
                {{ __('rsvp.will_you_attend') }} <span style="color:#ef4444;">*</span>
            </legend>
            @foreach (['yes' => 'rsvp.yes', 'no' => 'rsvp.no', 'maybe' => 'rsvp.maybe'] as $val => $key)
                <label style="display:flex; align-items:center; gap:0.5rem; padding:0.375rem 0; cursor:pointer; font-size:0.875rem; color:#374151;">
                    <input type="radio" name="status" value="{{ $val }}" required
                           {{ old('status', $existingRsvp?->status) === $val ? 'checked' : '' }}>
                    {{ __($key) }}
                </label>
            @endforeach
            @error('status')
                <span style="color:#ef4444; font-size:0.75rem;">{{ $message }}</span>
            @enderror
        </fieldset>

        <div style="margin-bottom:1rem;">
            <label style="display:block; font-size:0.875rem; font-weight:500; color:#374151; margin-bottom:0.375rem;">
                {{ __('rsvp.plus_one') }}
            </label>
            <input type="number" name="plus_one"
                   value="{{ old('plus_one', $existingRsvp?->plus_one ?? 0) }}"
                   min="0" max="10"
                   style="width:100px; padding:0.5rem 0.75rem; border:1px solid #d1d5db; border-radius:0.5rem; font-size:0.875rem; box-sizing:border-box;">
        </div>

        <div style="margin-bottom:1.5rem;">
            <label style="display:block; font-size:0.875rem; font-weight:500; color:#374151; margin-bottom:0.375rem;">
                {{ __('rsvp.dietary') }}
            </label>
            <input type="text" name="dietary"
                   value="{{ old('dietary', $existingRsvp?->dietary) }}"
                   maxlength="200"
                   style="width:100%; padding:0.5rem 0.75rem; border:1px solid #d1d5db; border-radius:0.5rem; font-size:0.875rem; box-sizing:border-box;">
        </div>

        <button type="submit" style="
            width:100%; padding:0.75rem; background:#4f46e5; color:#fff;
            border:none; border-radius:0.5rem; font-size:0.875rem; font-weight:600;
            cursor:pointer;
        ">
            {{ __('rsvp.submit') }}
        </button>
    </form>

</div>
</div>
@endsection
