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
      alt="プロフィール画像"
      class="avatar avatar--lg"
      style="width:120px;height:120px;border-radius:50%;object-fit:cover">

    <div class="mypage__info">
      <h1 class="mypage__name">{{ auth()->user()->name }}</h1>
      <a href="{{ route('mypage.profile.edit') }}" class="btn btn--outline">プロフィールを編集</a>
    </div>
  </div>

  @php
    $currentTab = $tab ?? request('tab','selling');
  @endphp

  <div class="mypage__tabs">
    <a href="{{ route('mypage', ['tab'=>'selling']) }}"
       class="tab {{ $currentTab==='selling' ? 'tab--active' : '' }}">出品した商品</a>
    <a href="{{ route('mypage', ['tab'=>'purchased']) }}"
       class="tab {{ $currentTab==='purchased' ? 'tab--active' : '' }}">購入した商品</a>
  </div>

  <div class="mypage__items">
    @php
      $list = $currentTab==='selling' ? $sellingItems : $purchasedItems;
    @endphp

    @forelse($list as $row)
      @php
        // sellingItems は Item, purchasedItems は Purchase（→ item 経由）
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
        {{ $currentTab==='selling' ? 'まだ出品はありません。' : 'まだ購入はありません。' }}
      </div>
    @endforelse
  </div>
</div>
@endsection
