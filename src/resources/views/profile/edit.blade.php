@extends('layouts.app')
@section('title','プロフィール設定')

@section('content')
<h1 class="page-title center">プロフィール設定</h1>

<form class="form form--narrow"
      method="POST"
      action="{{ route('mypage.profile.update') }}"
      enctype="multipart/form-data">
  @csrf
  @method('PUT')   {{-- ルートを PUT にしてる前提 --}}

  <div class="profile">
    <div class="avatar">
      <img class="avatar__img" src="{{ auth()->user()->profile_image_path ? asset('storage/'.auth()->user()->profile_image_path) : asset('img/avatar.png') }}" alt="">
    </div>
    <label class="btn btn--outline btn--small" for="profile_image">画像を選択する</label>
    <input class="hidden" id="profile_image" type="file" name="profile_image" accept="image/png,image/jpeg">
  </div>

  <div class="field">
    <label class="label">ユーザー名</label>
    <input class="input" type="text" name="name" value="{{ old('name', auth()->user()->name) }}">
    @error('name')<div class="error">{{ $message }}</div>@enderror
  </div>

  <div id="address-form" class="section">
    <div class="field">
      <label class="label">郵便番号</label>
      <input class="input" type="text" name="postal_code" value="{{ old('postal_code', auth()->user()->postal_code) }}">
      @error('postal_code')<div class="error">{{ $message }}</div>@enderror
    </div>
    <div class="field">
      <label class="label">住所</label>
      <input class="input" type="text" name="address" value="{{ old('address', auth()->user()->address) }}">
      @error('address')<div class="error">{{ $message }}</div>@enderror
    </div>
    <div class="field">
      <label class="label">建物名</label>
      <input class="input" type="text" name="building" value="{{ old('building', auth()->user()->building) }}">
      @error('building')<div class="error">{{ $message }}</div>@enderror
    </div>
  </div>

  <button class="btn btn--primary full mt-md" type="submit">更新する</button>
</form>
@endsection
