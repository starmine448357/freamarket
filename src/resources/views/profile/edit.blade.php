@extends('layouts.app')
@section('title','プロフィール設定')
@section('page_css')
<link rel="stylesheet" href="{{ asset('css/profile.css') }}">
@endsection

@section('content')
<div class="profile">
  <h1 class="profile__title">プロフィール設定</h1>

  <form class="profile__form" method="POST" action="{{ route('mypage.profile.update') }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    {{-- アイコン画像 --}}
    <div class="profile__avatar">
      <div class="avatar avatar--lg"></div>
      <label for="image" class="btn btn--outline">画像を選択する</label>
      <input type="file" name="image" id="image" class="hidden" accept="image/png,image/jpeg">
      @error('image')<div class="error">{{ $message }}</div>@enderror
    </div>

    {{-- ユーザー名 --}}
    <div class="form-group">
      <label for="name">ユーザー名</label>
      <input type="text" id="name" name="name" value="{{ old('name', auth()->user()->name) }}">
      @error('name')<div class="error">{{ $message }}</div>@enderror
    </div>

    {{-- 郵便番号 --}}
    <div class="form-group">
      <label for="postal_code">郵便番号</label>
      <input type="text" id="postal_code" name="postal_code" value="{{ old('postal_code', auth()->user()->postal_code) }}">
      @error('postal_code')<div class="error">{{ $message }}</div>@enderror
    </div>

    {{-- 住所 --}}
    <div class="form-group">
      <label for="address">住所</label>
      <input type="text" id="address" name="address" value="{{ old('address', auth()->user()->address) }}">
      @error('address')<div class="error">{{ $message }}</div>@enderror
    </div>

    {{-- 建物名 --}}
    <div class="form-group">
      <label for="building">建物名</label>
      <input type="text" id="building" name="building" value="{{ old('building', auth()->user()->building) }}">
      @error('building')<div class="error">{{ $message }}</div>@enderror
    </div>

    {{-- 更新ボタン --}}
    <button type="submit" class="btn btn--primary full">更新する</button>
  </form>
</div>
@endsection
