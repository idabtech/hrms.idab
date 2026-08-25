@extends('layouts.auth')
@section('page-title')
    {{ __('Verify Email') }}
@endsection
@section('language-bar')
    @php
        $languages = App\Models\Utility::languages();
        if (empty($lang)) {
            $lang = App\Models\Utility::getValByName('default_language');
        }
    @endphp
    <div class="lang-dropdown-only-desk">
        <li class="dropdown dash-h-item drp-language">
            <a class="dash-head-link dropdown-toggle btn" href="javascript:void(0)" data-bs-toggle="dropdown" aria-expanded="false">
                <span class="drp-text"> {{ ucFirst($languages[$lang]) }}
                </span>
            </a>
            <div class="dropdown-menu dash-h-dropdown dropdown-menu-end">
                @foreach ($languages as $code => $language)
                    <a href="{{ url('/verify-email', $code) }}" tabindex="0"
                        class="dropdown-item {{ $code == $lang ? 'active' : '' }}">
                        <span>{{ ucFirst($language) }}</span>
                    </a>
                @endforeach
            </div>
        </li>
    </div>
@endsection
@section('content')
    <div class="card-body">
        <div>
            <h2 class="auth-card-title">{{ __('Verify Email') }}</h2>
        </div>
        @if (session('status') == 'verification-link-sent')
            <div class="mb-4 font-medium text-sm text-green-600 text-primary">
                {{ __('A new verification link has been sent to the email address you provided during registration.') }}
            </div>
        @endif
        <div class="mb-4 text-sm text-gray-600">
            {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
        </div>
        <div class="custom-login-form">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <div class="d-grid mb-3">
                    <button class="btn btn-primary-custom" type="submit">
                        {{ __('Resend Verification Email') }}
                    </button>
                </div>
            </form>
            <form method="POST" action="{{ route('logout') }}" class="text-center">
                @csrf

                <button type="submit" class="btn btn-link text-muted text-sm">
                    {{ __('Logout') }}
                </button>
            </form>
        </div>
    </div>
@endsection
