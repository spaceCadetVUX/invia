@extends('layouts.minimal')

@section('content')
<div style="max-width:480px; margin:3rem auto; padding:0 1rem; text-align:center;">
    <div style="font-size:3rem; margin-bottom:1rem;">🎉</div>
    <h1 style="font-size:1.25rem; margin-bottom:0.5rem;">Đăng ký thành công!</h1>
    <p style="color:#6b7280; font-size:0.875rem; margin-bottom:2rem;">
        Cảm ơn bạn đã đăng ký tham dự <strong>{{ $event->title }}</strong>.
        Chúng tôi sẽ liên hệ với bạn sớm nhất.
    </p>
    <a href="{{ route('invitation.show', $event->slug) }}"
       style="display:inline-block; padding:0.625rem 1.5rem; background:#4f46e5; color:#fff; border-radius:0.5rem; font-size:0.875rem; text-decoration:none;">
        Xem thiệp mời
    </a>
</div>
@endsection
