@extends('layouts.minimal')

@section('title', __('rsvp.confirm') . ' — ' . $event->title)

@section('content')
<div style="
    min-height:100vh; display:flex; align-items:center; justify-content:center;
    flex-direction:column; text-align:center; padding:2rem; background:#f9fafb;
">
    @php
        $icons   = ['yes' => '🎉', 'no' => '😢', 'maybe' => '🤔'];
        $labels  = ['yes' => 'Tôi sẽ tham dự', 'no' => 'Tôi không thể tham dự', 'maybe' => 'Có thể tham dự'];
        $icon    = $icons[$rsvp_status]  ?? '✅';
        $label   = $labels[$rsvp_status] ?? 'Đã ghi nhận';
    @endphp

    <div style="font-size:3.5rem; margin-bottom:1rem;">{{ $icon }}</div>

    <h1 style="font-size:1.5rem; font-weight:700; margin-bottom:0.5rem; color:#111827;">
        {{ __('rsvp.submit') }} thành công
    </h1>
    <p style="color:#6b7280; margin-bottom:0.5rem; font-size:0.95rem;">
        Trạng thái: <strong>{{ $label }}</strong>
    </p>
    <p style="color:#9ca3af; font-size:0.875rem; margin-bottom:2rem;">
        {{ $event->title }} — {{ $event->venue_name }}, {{ $event->event_date->format('d/m/Y') }}
    </p>

    <a href="{{ route('invitation.show', $event->slug) }}" style="
        display:inline-flex; align-items:center; padding:0.625rem 1.5rem;
        background:#4f46e5; color:#fff; border-radius:0.5rem;
        text-decoration:none; font-size:0.875rem; font-weight:500;
    ">Quay lại thiệp mời</a>
</div>
@endsection
