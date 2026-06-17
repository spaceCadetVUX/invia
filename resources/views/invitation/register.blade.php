@extends('layouts.minimal')

@section('content')
<div style="max-width:480px; margin:3rem auto; padding:0 1rem;">
    <h1 style="text-align:center; font-size:1.25rem; margin-bottom:0.5rem;">{{ $event->title }}</h1>
    <p style="text-align:center; color:#6b7280; font-size:0.875rem; margin-bottom:2rem;">
        Đăng ký tham dự sự kiện
    </p>

    <form method="POST" action="{{ route('invitation.register.store', $event->slug) }}"
          style="background:#fff; border:1px solid #e5e7eb; border-radius:0.75rem; padding:1.5rem;">
        @csrf

        <div style="margin-bottom:1rem;">
            <label style="display:block; font-size:0.875rem; color:#374151; margin-bottom:0.25rem;">
                Họ và tên <span style="color:#ef4444;">*</span>
            </label>
            <input type="text" name="name" value="{{ old('name') }}" required maxlength="100"
                   style="width:100%; padding:0.5rem 0.75rem; border:1px solid #d1d5db; border-radius:0.5rem; font-size:0.875rem; box-sizing:border-box;">
            @error('name')
                <span style="font-size:0.75rem; color:#ef4444;">{{ $message }}</span>
            @enderror
        </div>

        <div style="margin-bottom:1rem;">
            <label style="display:block; font-size:0.875rem; color:#374151; margin-bottom:0.25rem;">
                Email
            </label>
            <input type="email" name="email" value="{{ old('email') }}" maxlength="150"
                   style="width:100%; padding:0.5rem 0.75rem; border:1px solid #d1d5db; border-radius:0.5rem; font-size:0.875rem; box-sizing:border-box;">
            @error('email')
                <span style="font-size:0.75rem; color:#ef4444;">{{ $message }}</span>
            @enderror
        </div>

        <div style="margin-bottom:1.5rem;">
            <label style="display:block; font-size:0.875rem; color:#374151; margin-bottom:0.25rem;">
                Số điện thoại
            </label>
            <input type="text" name="phone" value="{{ old('phone') }}" maxlength="20"
                   style="width:100%; padding:0.5rem 0.75rem; border:1px solid #d1d5db; border-radius:0.5rem; font-size:0.875rem; box-sizing:border-box;">
        </div>

        <button type="submit" style="
            width:100%; padding:0.625rem;
            background:#4f46e5; color:#fff;
            border:none; border-radius:0.5rem;
            font-size:0.875rem; font-weight:600; cursor:pointer;
        ">Đăng ký</button>
    </form>
</div>
@endsection
