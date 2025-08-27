@extends('layouts.auth') {{-- ロゴだけのレイアウトを使う --}}

@section('title', 'ログイン')

@section('content')
  <h1 class="page-title page-title--center">ログイン</h1>

  <form class="card card--pad form form--narrow" method="POST" action="{{ route('login') }}">
    @csrf

    {{-- メールアドレス --}}
    <div class="form-field">
      <label for="email" class="form-label">メールアドレス</label>
      <input id="email" class="form-input" type="email" name="email" value="{{ old('email') }}" required autofocus>
      @error('email')
        <div class="form-error">{{ $message }}</div>
      @enderror
    </div>

    {{-- パスワード --}}
    <div class="form-field">
      <label for="password" class="form-label">パスワード</label>
      <input id="password" class="form-input" type="password" name="password" required>
      @error('password')
        <div class="form-error">{{ $message }}</div>
      @enderror
    </div>

    {{-- ログインボタン --}}
    <button class="btn btn--primary btn--full" type="submit">ログインする</button>

    {{-- 会員登録リンク --}}
    <div class="text-center mt-sm">
      <a class="link link--blue" href="{{ route('register') }}">会員登録はこちら</a>
    </div>
  </form>
@endsection
