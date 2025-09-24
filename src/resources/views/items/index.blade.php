@extends('layouts.app')

@section('title','商品一覧')

@section('page_css')
  @php
    $css = public_path('css/items.css');
    $ver = file_exists($css) ? filemtime($css) : time();
    $currentTab = $tab ?? 'recommend';
  @endphp
  <link rel="stylesheet" href="{{ asset('css/items.css') }}?v={{ $ver }}">
@endsection

@section('content')
  <div class="tabs">
    <a href="{{ route('items.index') }}"
       class="tab {{ $currentTab === 'recommend' ? 'tab--active' : '' }}">
      おすすめ
    </a>

    <a href="{{ route('items.index', ['tab' => 'mylist']) }}"
       class="tab {{ $currentTab === 'mylist' ? 'tab--active' : '' }}">
      マイリスト
    </a>
  </div>
  <hr class="divider">

  @if($items->isEmpty())
    <p class="muted mt-sm">
      {{ $currentTab === 'mylist' ? 'マイリストに商品がありません。' : '該当する商品がありません。' }}
    </p>
  @else
    <div class="grid grid--4 mt-md">
      @foreach($items as $item)
        <a class="item-link" href="{{ route('items.show', $item) }}">
          <div class="item-thumb">
            <img
              class="item-img"
              src="{{ $item->image_url }}"
              alt="{{ $item->title }}"
              loading="lazy"
              decoding="async"
            >
            @if($item->is_sold)
              <img class="sold-sticker" src="{{ asset('images/sold.png') }}" alt="売り切れ">
            @endif
          </div>
          <div class="item-title">{{ $item->title }}</div>
          <div class="item-price">¥{{ number_format($item->price) }}</div>
        </a>
      @endforeach
    </div>

    <div class="mt-md">
      {{ $items->appends(request()->query())->links('vendor.pagination.default') }}
    </div>
  @endif
@endsection
