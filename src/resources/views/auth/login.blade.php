@extends('layouts.app')
@section('title','ログイン')

@section('content')
<h1 class="page-title center">ログイン</h1>

<form class="card card--pad form form--narrow" method="POST" action="{{ route('login') }}">
  @csrf
  <div class="field">
    <label class="label">メールアドレス</label>
    <input class="input" type="email" name="email" value="{{ old('email') }}">
    @error('email')<div class="error">{{ $message }}</div>@enderror
  </div>

  <div class="field">
    <label class="label">パスワード</label>
    <input class="input" type="password" name="password">
    @error('password')<div class="error">{{ $message }}</div>@enderror
  </div>

  <button class="btn btn--primary full" type="submit">ログインする</button>
  <div class="center mt-sm"><a class="link link--blue" href="{{ route('register') }}">会員登録はこちら</a></div>
</form>
@endsection
