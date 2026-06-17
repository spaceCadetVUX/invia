@extends('layouts.minimal')

@section('title', __('invitation.expired_title'))

@section('content')
<div style="
    min-height:100vh; display:flex; align-items:center; justify-content:center;
    flex-direction:column; text-align:center; padding:2rem;
">
    <div style="font-size:3rem; margin-bottom:1rem;">📬</div>
    <h1 style="font-size:1.5rem; font-weight:600; margin-bottom:0.5rem;">
        {{ __('invitation.expired_title') }}
    </h1>
    <p style="color:#6b7280; margin-bottom:2rem;">
        Thiệp mời <strong>{{ $event->title }}</strong> đã không còn hoạt động.
    </p>
    <a href="{{ route('home') }}" style="
        display:inline-flex; align-items:center; padding:0.625rem 1.5rem;
        background:#4f46e5; color:#fff; border-radius:0.5rem;
        text-decoration:none; font-size:0.875rem; font-weight:500;
    ">Tạo thiệp mời của bạn</a>
</div>
@endsection
