@extends('layouts.public')
@section('title', 'Member login')
@section('content')
<section class="login-page">
    <div class="login-aside">
        <p class="eyebrow eyebrow-light">Members’ access</p>
        <h1>Welcome<br><em>back.</em></h1>
        <p>Your place in the Den is ready when you are.</p>
    </div>

    <div class="login-form-wrap">
        <form class="login-form" method="POST" action="{{ route('login.store') }}" x-data="{ showPassword: false }">
            @csrf
            <p class="eyebrow">Member portal</p>
            <h2>Sign in</h2>
            <p class="login-intro">Enter your email and password to continue to your account.</p>

            <label for="email">Email address</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="username" inputmode="email" autocapitalize="none" spellcheck="false" required autofocus>
            @error('email')<span class="field-error" role="alert">{{ $message }}</span>@enderror

            <label for="password">Password</label>
            <div class="password-input-wrap">
                <input id="password" name="password" :type="showPassword ? 'text' : 'password'" autocomplete="current-password" required>
                <button class="password-toggle" type="button" @click="showPassword = !showPassword" :aria-pressed="showPassword.toString()" :aria-label="showPassword ? 'Hide password' : 'Show password'" x-text="showPassword ? 'Hide' : 'Show'">Show</button>
            </div>
            @error('password')<span class="field-error" role="alert">{{ $message }}</span>@enderror

            <div class="form-check">
                <input id="remember" name="remember" type="checkbox" value="1">
                <label for="remember">Remember me</label>
            </div>
            <button class="button button-full" type="submit">Continue to the Den <span aria-hidden="true">→</span></button>
            <p class="form-note">Access is for active members and authorized team accounts.</p>
        </form>
    </div>
</section>
@endsection
