@extends('layouts.app')
@section('title','商品一覧')
@section('page_css')
<link rel="stylesheet" href="{{ asset('css/items.css') }}">
@endsection

@section('content')
<div class="tabs">
  <a href="{{ route('items.index') }}"
     class="tab {{ ($tab ?? 'recommend') === 'recommend' ? 'tab--active' : '' }}">
    おすすめ
  </a>
  <a href="{{ route('items.mylist') }}"
     class="tab {{ ($tab ?? 'recommend') === 'mylist' ? 'tab--active' : '' }}">
    マイリスト
  </a>
</div>
<hr class="divider">

@if($items->count() === 0)
  <p class="muted mt-sm">
    @if(($tab ?? 'recommend') === 'mylist')
      マイリストに商品がありません。
    @else
      該当する商品がありません。
    @endif
  </p>
@else
<div class="grid grid--4 mt-md">
  @foreach($items as $item)
  <a class="item-link" href="{{ route('items.show', $item) }}">
    <img class="item-img" src="{{ $item->image_url }}" alt="">
    <div class="item-title">{{ $item->title }}</div>
  </a>
  @endforeach
</div>

  <div class="pagination mt-md">
    {{ $items->links() }}
  </div>
@endif
@endsection
