{{-- resources/views/auth/verify-email.blade.php --}}
@extends('layouts.app')
@section('title','メール認証')

@section('content')
  <div class="vfy">
    <p class="vfy__title">
      登録していただいたメールアドレスに認証メールを送付しました。<br>
      メール認証を完了してください。
    </p>

  <a href="{{ route('mypage.profile.edit') }}" class="vfy__primary-btn">
      認証はこちらから
    </a>

    <form method="POST" action="{{ route('verification.send') }}" class="vfy__resend">
      @csrf
      <button type="submit" class="vfy__resend-link">認証メールを再送する</button>
    </form>

    @if (session('message'))
      <div class="vfy__flash">{{ session('message') }}</div>
    @endif
  </div>
@endsection
