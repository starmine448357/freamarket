@extends('layouts.auth')

@section('title', 'ログイン')

@section('content')
  <h1 class="auth-title">ログイン</h1>

  <form class="auth-container" method="POST" action="{{ route('login') }}" novalidate>
    @csrf

    {{-- メールアドレス --}}
    <div class="form-field">
      <label for="email" class="auth-label">メールアドレス</label>
      <input
        id="email"
        type="email"
        name="email"
        value="{{ old('email') }}"
        class="auth-input {{ $errors->has('email') ? 'is-invalid' : '' }}"
        required
        autofocus
        autocomplete="username"
      >
      @error('email')
        <div class="form-error">{{ $message }}</div>
      @enderror
    </div>

    {{-- パスワード --}}
    <div class="form-field">
      <label for="password" class="auth-label">パスワード</label>
      <input
        id="password"
        type="password"
        name="password"
        class="auth-input {{ $errors->has('password') ? 'is-invalid' : '' }}"
        required
        autocomplete="current-password"
      >
      @error('password')
        <div class="form-error">{{ $message }}</div>
      @enderror
    </div>

    {{-- ログインボタン --}}
    <button class="auth-button" type="submit">ログインする</button>

    {{-- 会員登録リンク --}}
    <div class="text-center mt-sm">
      <a class="link link--blue" href="{{ route('register') }}">会員登録はこちら</a>
    </div>
  </form>
@endsection
