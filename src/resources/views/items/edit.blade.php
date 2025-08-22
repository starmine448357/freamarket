@extends('layouts.app')
@section('title','商品を編集')
@section('page_css')<link rel="stylesheet" href="{{ asset('css/items.css') }}">@endsection

@section('content')
<h1 class="page-title center">商品を編集</h1>

<form class="form form--narrow" method="POST" action="{{ route('items.update',$item) }}" enctype="multipart/form-data">
  @csrf @method('PUT')

  <div class="section">
    <div class="label label--bold">現在の画像</div>
    <img class="img-preview mt-xs" src="{{ $item->image_path ? asset('storage/'.$item->image_path) : asset('img/placeholder.png') }}" alt="">
  </div>

  <div class="section">
    <div class="label">画像を変更</div>
    <label class="btn btn--outline btn--small mt-xs" for="image">画像を選択する</label>
    <input class="hidden" id="image" type="file" name="image" accept="image/png,image/jpeg">
    @error('image')<div class="error">{{ $message }}</div>@enderror
  </div>

  <div class="section">
    <div class="label label--bold mb-xs">商品名と説明</div>

    <div class="field">
      <label class="label">商品名</label>
      <input class="input" type="text" name="title" value="{{ old('title', $item->title) }}">
      @error('title')<div class="error">{{ $message }}</div>@enderror
    </div>

    <div class="field">
      <label class="label">ブランド名</label>
      <input class="input" type="text" name="brand" value="{{ old('brand', $item->brand) }}">
      @error('brand')<div class="error">{{ $message }}</div>@enderror
    </div>

    <div class="field">
      <label class="label">商品の説明</label>
      <textarea class="textarea" name="description" rows="5">{{ old('description', $item->description) }}</textarea>
      @error('description')<div class="error">{{ $message }}</div>@enderror
    </div>

    <div class="row">
      <div class="field">
        <label class="label">販売価格</label>
        <input class="input" type="number" name="price" value="{{ old('price', $item->price) }}">
        @error('price')<div class="error">{{ $message }}</div>@enderror
      </div>
      <div class="field">
        <label class="label">状態</label>
        <select class="select" name="condition">
          @foreach(['new'=>'新品','like_new'=>'未使用に近い','used'=>'中古'] as $k=>$v)
            <option value="{{ $k }}" @selected(old('condition',$item->condition)===$k)>{{ $v }}</option>
          @endforeach
        </select>
        @error('condition')<div class="error">{{ $message }}</div>@enderror
      </div>
    </div>
  </div>

  <div class="section">
    <div class="label label--bold">カテゴリ</div>
    <div class="chips">
      @foreach($categories as $cat)
        <label class="chip chip--select">
          <input class="checkbox" type="checkbox" name="categories[]" value="{{ $cat->id }}" @checked(in_array($cat->id, $selected))>
          <span class="chip__text">{{ $cat->name }}</span>
        </label>
      @endforeach
    </div>
    @error('categories.*')<div class="error">{{ $message }}</div>@enderror
  </div>

  <button class="btn btn--primary full mt-md" type="submit">更新する</button>
</form>
@endsection
