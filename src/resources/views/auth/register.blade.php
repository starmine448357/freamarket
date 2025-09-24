@extends('layouts.auth') {{-- ロゴだけのレイアウト --}}

@section('title', '会員登録')

@section('content')
  <h1 class="auth-title">会員登録</h1>

  {{-- ブラウザのネイティブ検証を無効化 --}}
  <form class="auth-container"
        method="POST"
        action="{{ route('register') }}"
        novalidate>
    @csrf

    {{-- ユーザー名 --}}
    <div>
      <label for="name" class="auth-label">ユーザー名</label>
      <input
        id="name"
        type="text"
        name="name"
        value="{{ old('name') }}"
        class="auth-input"
        required
        autocomplete="name"
      >
      @error('name')
        <div class="form-error">{{ $message }}</div>
      @enderror
    </div>

    {{-- メールアドレス --}}
    <div>
      <label for="email" class="auth-label">メールアドレス</label>
      <input
        id="email"
        type="email"
        name="email"
        value="{{ old('email') }}"
        class="auth-input"
        required
        autocomplete="email"
      >
      @error('email')
        <div class="form-error">{{ $message }}</div>
      @enderror
    </div>

    {{-- パスワード --}}
    <div>
      <label for="password" class="auth-label">パスワード</label>
      <input
        id="password"
        type="password"
        name="password"
        class="auth-input"
        required
        autocomplete="new-password"
      >
      @error('password')
        <div class="form-error">{{ $message }}</div>
      @enderror
    </div>

    {{-- 確認用パスワード --}}
    <div>
      <label for="password_confirmation" class="auth-label">確認用パスワード</label>
      <input
        id="password_confirmation"
        type="password"
        name="password_confirmation"
        class="auth-input"
        required
        autocomplete="new-password"
      >
      @error('password_confirmation')
        <div class="form-error">{{ $message }}</div>
      @enderror
    </div>

    {{-- 登録ボタン --}}
    <button class="auth-button" type="submit">登録する</button>

    {{-- ログインリンク --}}
    <div class="text-center">
      <a class="link link--blue" href="{{ route('login') }}">ログインはこちら</a>
    </div>
  </form>
@endsection
