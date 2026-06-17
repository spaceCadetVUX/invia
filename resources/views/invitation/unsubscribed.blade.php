@extends('layouts.minimal')

@section('title', __('email.unsubscribed_title'))

@section('content')
<div style="
    min-height:100vh; display:flex; align-items:center; justify-content:center;
    flex-direction:column; text-align:center; padding:2rem;
">
    <div style="font-size:3rem; margin-bottom:1rem;">✉️</div>
    <h1 style="font-size:1.5rem; font-weight:600; margin-bottom:0.5rem;">
        {{ __('email.unsubscribed_title') }}
    </h1>
    <p style="color:#6b7280; max-width:400px;">
        {{ __('email.unsubscribed_body', ['name' => $guest->name]) }}
    </p>
</div>
@endsection
