@extends('layouts.app')
@section('title','会員登録')

@section('content')
<h1 class="page-title center">会員登録</h1>

<form class="card card--pad form form--narrow" method="POST" action="{{ route('register') }}">
  @csrf
  <div class="field">
    <label class="label">ユーザー名</label>
    <input class="input" type="text" name="name" value="{{ old('name') }}">
    @error('name')<div class="error">{{ $message }}</div>@enderror
  </div>

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

  <div class="field">
    <label class="label">確認用パスワード</label>
    <input class="input" type="password" name="password_confirmation">
  </div>

  <button class="btn btn--primary full" type="submit">登録する</button>
  <div class="center mt-sm"><a class="link link--blue" href="{{ route('login') }}">ログインはこちら</a></div>
</form>
@endsection
