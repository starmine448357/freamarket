@extends('layouts.app')
@section('title','商品一覧')
@section('page_css')
@php
  $css = public_path('css/items.css');
  $ver = file_exists($css) ? filemtime($css) : time();
@endphp
<link rel="stylesheet" href="{{ asset('css/items.css') }}?v={{ $ver }}">
@endsection

@section('content')
<div class="tabs">
  {{-- おすすめタブ --}}
  <a href="{{ route('items.index') }}"
     class="tab {{ ($tab ?? 'recommend') === 'recommend' ? 'tab--active' : '' }}">
    おすすめ
  </a>

  {{-- マイリストタブ（?tab=mylist） --}}
  <a href="{{ route('items.index', ['tab' => 'mylist']) }}"
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
      @php
        $isSold = ($item->is_sold ?? false) || (($item->status ?? null) === 'sold');
      @endphp

      <a class="item-link" href="{{ route('items.show', $item) }}">
        <div class="item-thumb">
          <img
            class="item-img"
            src="{{ $item->image_url }}"
            alt="{{ $item->title }}"
            loading="lazy"
            decoding="async"
          >
          @if($isSold)
            <img class="sold-sticker" src="{{ asset('images/sold.png') }}" alt="SOLD">
          @endif
        </div>
        <div class="item-title">{{ $item->title }}</div>
        <div class="item-price">¥{{ number_format($item->price) }}</div>
      </a>
    @endforeach
  </div>

  <div class="mt-md">
    {{-- ページネーションもクエリを保持 --}}
    {{ $items->appends(request()->query())->links('vendor.pagination.default') }}
  </div>
@endif
@endsection
