@extends('layouts.app')
@section('title','商品の出品')
@section('page_css')<link rel="stylesheet" href="{{ asset('css/items.css') }}">@endsection

@section('content')
<h1 class="page-title center">商品の出品</h1>

<form class="form form--narrow" method="POST" action="{{ route('items.store') }}" enctype="multipart/form-data">
  @csrf

  <div class="section">
    <div class="label label--bold">商品画像</div>
    <div class="dropzone mt-xs">
      <label class="btn btn--outline btn--small" for="image">画像を選択する</label>
      <input class="hidden" id="image" type="file" name="image" accept="image/png,image/jpeg">
    </div>
    @error('image')<div class="error">{{ $message }}</div>@enderror
  </div>

  <div class="section">
    <div class="label label--bold mb-xs">商品の詳細</div>
    <div class="label">カテゴリー</div>
    <div class="chips">
      @foreach($categories as $cat)
        <label class="chip chip--select">
          <input class="checkbox" type="checkbox" name="categories[]" value="{{ $cat->id }}" @checked(collect(old('categories',[]))->contains($cat->id))>
          <span class="chip__text">{{ $cat->name }}</span>
        </label>
      @endforeach
    </div>
    @error('categories.*')<div class="error">{{ $message }}</div>@enderror
  </div>

  <div class="section">
    <div class="label">商品の状態</div>
    <select class="select" name="condition">
      <option value="">選択してください</option>
      <option value="new" @selected(old('condition')==='new')>新品</option>
      <option value="like_new" @selected(old('condition')==='like_new')>未使用に近い</option>
      <option value="used" @selected(old('condition')==='used')>中古</option>
    </select>
    @error('condition')<div class="error">{{ $message }}</div>@enderror
  </div>

  <div class="section">
    <div class="label label--bold mb-xs">商品名と説明</div>

    <div class="field">
      <label class="label">商品名</label>
      <input class="input" type="text" name="title" value="{{ old('title') }}">
      @error('title')<div class="error">{{ $message }}</div>@enderror
    </div>

    <div class="field">
      <label class="label">ブランド名</label>
      <input class="input" type="text" name="brand" value="{{ old('brand') }}">
      @error('brand')<div class="error">{{ $message }}</div>@enderror
    </div>

    <div class="field">
      <label class="label">商品の説明</label>
      <textarea class="textarea" name="description" rows="5">{{ old('description') }}</textarea>
      @error('description')<div class="error">{{ $message }}</div>@enderror
    </div>

    <div class="field">
      <label class="label">販売価格</label>
      <input class="input" type="number" name="price" value="{{ old('price') }}">
      @error('price')<div class="error">{{ $message }}</div>@enderror
    </div>
  </div>

  <button class="btn btn--primary full mt-md" type="submit">出品する</button>
</form>
@endsection
