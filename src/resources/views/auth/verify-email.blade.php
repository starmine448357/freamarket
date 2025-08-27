@extends('layouts.auth')
@section('title', 'メール認証')

@section('content')
  <div class="auth-container" style="text-align:center;">
    <p>
      登録していただいたメールアドレスに認証メールを送付しました。<br>
      メール認証を完了してください。
    </p>

    {{-- グレーのボタン --}}
    <a href="{{ route('mypage.profile.edit') }}" class="btn btn--secondary btn--full" style="margin:20px 0;">
      認証はこちらから
    </a>

    {{-- 再送はリンク見た目（POST送信） --}}
{{-- 再送リンク（リンク風のbutton） --}}
<form method="POST" action="{{ route('verification.send') }}">
  @csrf
  <button type="submit" class="link link--blue as-link">
    認証メールを再送する
  </button>
</form>
  </div>
@endsection
