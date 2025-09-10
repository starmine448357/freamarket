@extends('layouts.auth') {{-- ロゴだけのレイアウト --}}

@section('title', '会員登録')

@section('content')
  <h1 class="page-title page-title--center">会員登録</h1>

  {{-- ★ ブラウザのネイティブ検証を無効化 --}}
  <form class="card card--pad form form--narrow" method="POST" action="{{ route('register') }}" novalidate>
    @csrf

    {{-- ユーザー名 --}}
    <div class="form-field">
      <label for="name" class="form-label">ユーザー名</label>
      <input id="name" class="form-input" type="text" name="name" value="{{ old('name') }}" required autocomplete="name">
      @error('name')
        <div class="form-error">{{ $message }}</div>
      @enderror
    </div>

    {{-- メールアドレス --}}
    <div class="form-field">
      <label for="email" class="form-label">メールアドレス</label>
      <input id="email" class="form-input" type="email" name="email" value="{{ old('email') }}" required autocomplete="email">
      @error('email')
        <div class="form-error">{{ $message }}</div>
      @enderror
    </div>

    {{-- パスワード --}}
    <div class="form-field">
      <label for="password" class="form-label">パスワード</label>
      <input id="password" class="form-input" type="password" name="password" required autocomplete="new-password">
      @error('password')
        <div class="form-error">{{ $message }}</div>
      @enderror
    </div>

    {{-- 確認用パスワード --}}
    <div class="form-field">
      <label for="password_confirmation" class="form-label">確認用パスワード</label>
      <input id="password_confirmation" class="form-input" type="password" name="password_confirmation" required autocomplete="new-password">
      {{-- ★ エラー表示を追加 --}}
      @error('password_confirmation')
        <div class="form-error">{{ $message }}</div>
      @enderror
    </div>

    {{-- 登録ボタン --}}
    <button class="btn btn--primary btn--full" type="submit">登録する</button>

    {{-- ログインリンク --}}
    <div class="text-center mt-sm">
      <a class="link link--blue" href="{{ route('login') }}">ログインはこちら</a>
    </div>
  </form>
@endsection
