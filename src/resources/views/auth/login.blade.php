@extends('layouts.auth') {{-- ロゴだけのレイアウトを使う --}}

@section('title', 'ログイン')

@section('content')
  <h1 class="page-title page-title--center">ログイン</h1>

  <form class="card card--pad form form--narrow"
        method="POST"
        action="{{ route('login') }}"
        novalidate>
    @csrf

    {{-- メールアドレス --}}
    <div class="form-field">
      <label for="email" class="form-label">メールアドレス</label>
      <input
        id="email"
        class="form-input {{ $errors->has('email') ? 'is-invalid' : '' }}"
        type="email"
        name="email"
        value="{{ old('email') }}"
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
      <label for="password" class="form-label">パスワード</label>
      <input
        id="password"
        class="form-input {{ $errors->has('password') ? 'is-invalid' : '' }}"
        type="password"
        name="password"
        required
        autocomplete="current-password"
      >
      @error('password')
        <div class="form-error">{{ $message }}</div>
      @enderror
    </div>

    <button class="btn btn--primary btn--full" type="submit">ログインする</button>

    <div class="text-center mt-sm">
      <a class="link link--blue" href="{{ route('register') }}">会員登録はこちら</a>
    </div>
  </form>
@endsection
