@extends('layouts.app')
@section('title','商品一覧')
@section('page_css')<link rel="stylesheet" href="{{ asset('css/items.css') }}">@endsection

@section('content')
<div class="tabs">
  <span class="tab tab--active">おすすめ</span>
  <span class="tab">マイリスト</span>
</div>
<hr class="divider">

@if($items->count()===0)
  <p class="muted mt-sm">該当する商品がありません。</p>
@else
  <div class="grid grid--4 mt-md">
    @foreach($items as $item)
      <a class="card card--link link" href="{{ route('items.show',$item) }}">
        <img class="thumb" src="{{ $item->image_path ? asset('storage/'.$item->image_path) : asset('img/placeholder.png') }}" alt="">
        <div class="card__content">
          <div class="card__title">{{ $item->title }}</div>
          <div class="row row--between">
            <div class="price">¥{{ number_format($item->price) }}</div>
            <div class="muted">{{ $item->brand }}</div>
          </div>
          <div class="chips mt-xs">
            @foreach($item->categories as $cat)
              <span class="chip">{{ $cat->name }}</span>
            @endforeach
          </div>
        </div>
      </a>
    @endforeach
  </div>
  <div class="pagination mt-md">{{ $items->links() }}</div>
@endif
@endsection
