@extends('layouts.app')
@section('title','商品の出品')
@section('page_css')
<link rel="stylesheet" href="{{ asset('css/exhibition.css') }}">
@endsection

@section('content')
<h1 class="page-title center">商品の出品</h1>

<form class="form form--narrow" method="POST" action="{{ route('items.store') }}" enctype="multipart/form-data" novalidate>
  @csrf

  {{-- 商品画像 --}}
  <div class="section">
    <label class="label label--bold" for="image">商品画像</label>
    <div class="dropzone mt-xs">
      <label class="btn btn--outline btn--small" for="image">画像を選択する</label>
      <input class="hidden" id="image" type="file" name="image" accept="image/png,image/jpeg">
    </div>
    @error('image')<div class="error">{{ $message }}</div>@enderror
  </div>

  {{-- カテゴリー --}}
{{-- カテゴリー --}}
<div class="section">
  <div class="section-title">商品の詳細</div>
  <div class="label">カテゴリー</div>

  {{-- ← これを追加：.chips コンテナ --}}
  <div class="chips">
    @foreach($categories as $cat)
      <label class="chip chip--select">
        <input
          class="checkbox"
          type="checkbox"
          name="categories[]"
          value="{{ $cat->id }}"
          @checked(collect(old('categories',[]))->contains($cat->id))
        >
        <span class="chip__text">{{ $cat->name }}</span>
      </label>
    @endforeach
  </div>
  {{-- ↑ ここまで .chips で囲む --}}  
</div>

{{-- エラー表示はセクションの外でOK --}}
@if($errors->has('categories') || $errors->has('categories.*'))
  <div class="error">{{ $errors->first('categories') ?? $errors->first('categories.*') }}</div>
@endif
  </div>

  {{-- 状態 --}}
  <div class="section">
    <label class="label" for="condition">商品の状態</label>
    <select class="select" id="condition" name="condition" aria-invalid="@error('condition')true @else false @enderror">
      <option value="">選択してください</option>
      <option value="new"       @selected(old('condition')==='new')>良好</option>
      <option value="like_new"  @selected(old('condition')==='like_new')>目立った汚れなし</option>
      <option value="used"      @selected(old('condition')==='used')>やや傷や汚れあり</option>
      <option value="bad"       @selected(old('condition')==='bad')>状態が悪い</option>
    </select>
    @error('condition')<div class="error">{{ $message }}</div>@enderror
  </div>

  {{-- 商品名・説明等 --}}
      <div class="section">
        <div class="section-title">商品名と説明</div>
        <div class="field">
          <label class="label">商品名</label>
          <input class="input" type="text" name="title" value="{{ old('title') }}">
        </div>
          <div class="field">
            <label class="label" for="brand">ブランド名</label>
            <input class="input" id="brand" type="text" name="brand" value="{{ old('brand') }}" placeholder="任意">
            @error('brand')<div class="error">{{ $message }}</div>@enderror
          </div>

    <div class="field">
      <label class="label" for="description">商品の説明</label>
      <textarea class="textarea" id="description" name="description" rows="5" placeholder="サイズ感や使用回数、注意点などを記載">{{ old('description') }}</textarea>
      @error('description')<div class="error">{{ $message }}</div>@enderror
    </div>

    <div class="field">
      <label class="label" for="price">販売価格</label>
      <div class="price-input">
        <span class="price-input__prefix">¥</span>
        <input class="input input--price" id="price" type="number" min="0" step="1" name="price" value="{{ old('price') }}" placeholder="0">
      </div>
      @error('price')<div class="error">{{ $message }}</div>@enderror
    </div>
  </div>

  <button class="btn btn--primary full mt-md" type="submit">出品する</button>
</form>
@endsection
