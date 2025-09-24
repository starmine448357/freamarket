@extends('layouts.auth')

@section('title', 'メール認証')

@section('content')
  <div class="auth-container text-center">
    <p>
      登録していただいたメールアドレスに認証メールを送付しました。<br>
      メール認証を完了してください。
    </p>

    {{-- 認証画面へ進むボタン（グレー） --}}
    <a href="{{ route('mypage.profile.edit') }}"
       class="btn btn--secondary btn--full"
       style="margin:20px 0;">
      認証はこちらから
    </a>

    {{-- 認証メール再送（リンク風ボタン） --}}
    <form method="POST" action="{{ route('verification.send') }}">
      @csrf
      <button type="submit" class="link link--blue as-link">
        認証メールを再送する
      </button>
    </form>
  </div>
@endsection
