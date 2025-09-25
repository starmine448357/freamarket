@extends('layouts.app')
@section('title','マイページ')
@section('page_css')
<link rel="stylesheet" href="{{ asset('css/mypage.css') }}">
@endsection

@section('content')
<div class="mypage">
  <div class="mypage__header">
    {{-- プロフィール画像 --}}
    <img 
      src="{{ auth()->user()->profile_image_path 
              ? asset('storage/'.auth()->user()->profile_image_path) 
              : asset('images/default-avatar.png') }}" 
      alt=""
      class="avatar avatar--lg"
      style="width:120px;height:120px;border-radius:50%;object-fit:cover">

    <div class="mypage__info">
      <h1 class="mypage__name">{{ auth()->user()->name }}</h1>
      <a href="{{ route('mypage.profile.edit') }}" class="btn btn--outline">プロフィールを編集</a>
    </div>
  </div>

  @php
    // デフォルトは 'sell'（出品した商品タブ）
    $currentTab = $tab ?? request('tab', 'sell');
  @endphp

  <div class="mypage__tabs">
    <a href="{{ route('mypage', array_merge(request()->query(), ['tab'=>'sell'])) }}"
       class="tab {{ $currentTab==='sell' ? 'tab--active' : '' }}">
      出品した商品
    </a>
    <a href="{{ route('mypage', array_merge(request()->query(), ['tab'=>'buy'])) }}"
       class="tab {{ $currentTab==='buy' ? 'tab--active' : '' }}">
      購入した商品
    </a>
  </div>

  <div class="mypage__items">
    @php
      // 出品 or 購入タブごとに表示データを切り替え
      $list = $currentTab === 'buy'
        ? ($purchasedItems ?? [])
        : ($sellingItems ?? []);
    @endphp

    @forelse($list as $row)
      @php
        // 出品商品は Item モデル, 購入商品は Purchase 経由で Item を参照
        $entity = isset($row->title) ? $row : ($row->item ?? null);
      @endphp

      @if($entity)
        <a href="{{ route('items.show', $entity->id) }}" class="item-link">
          <div class="item-thumb">
            <img src="{{ $entity->image_url }}"
                 alt="{{ $entity->title }}"
                 class="item-img">
          </div>
          <div class="product-info">
            <div class="product-name">{{ $entity->title }}</div>
            <div class="product-price">¥{{ number_format($entity->price) }}</div>
          </div>
        </a>
      @endif
    @empty
      <div class="muted">
        @if($currentTab==='buy')
          まだ購入はありません。
        @elseif($currentTab==='sell')
          まだ出品はありません。
        @endif
      </div>
    @endforelse
  </div>
</div>
@endsection
