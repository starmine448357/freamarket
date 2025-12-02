@extends('layouts.app')
@section('title','マイページ')

@section('page_css')
<link rel="stylesheet" href="{{ asset('css/mypage.css') }}">
@endsection

@section('content')

<div class="mypage">

  <div class="mypage__header">

    <div class="mypage__left">
      <img
        src="{{ auth()->user()->profile_image_path
                      ? asset('storage/'.auth()->user()->profile_image_path)
                      : asset('images/default-avatar.png') }}"
        alt=""
        class="avatar avatar--lg">

      <div class="mypage__user-block">
        <h1 class="mypage__name">{{ auth()->user()->name }}</h1>

        @if(!empty($ratingAvgRounded))
        <div class="rating-stars">
          @for ($i = 1; $i <= 5; $i++)
            <span class="star {{ $i <= $ratingAvgRounded ? 'star--on' : 'star--off' }}">★</span>
            @endfor
        </div>
        @endif
      </div>
    </div>

    <a href="{{ route('mypage.profile.edit') }}" class="btn btn--outline">
      プロフィールを編集
    </a>

  </div>
</div>

@php
$currentTab = request('tab', 'sell');

if ($currentTab === 'buy') {
$list = $purchasedItems ?? [];
} elseif ($currentTab === 'transaction') {
$list = $transactionItems ?? [];
} else {
$list = $sellingItems ?? [];
}
@endphp

<div class="mypage__tabs-wrapper">
  <div class="mypage__tabs">

    <a href="{{ route('mypage', ['tab'=>'sell']) }}"
      class="tab {{ $currentTab==='sell' ? 'tab--active' : '' }}">
      出品した商品
    </a>

    <a href="{{ route('mypage', ['tab'=>'buy']) }}"
      class="tab {{ $currentTab==='buy' ? 'tab--active' : '' }}">
      購入した商品
    </a>

    <a href="{{ route('mypage', ['tab'=>'transaction']) }}"
      class="tab {{ $currentTab==='transaction' ? 'tab--active' : '' }}">
      取引中の商品

      @if(isset($totalUnread) && $totalUnread > 0)
      <span class="tab-badge">{{ $totalUnread }}</span>
      @endif
    </a>

  </div>
</div>

<div class="mypage">
  <div class="mypage__items">

    @forelse ($list as $row)

    @php
    $item = isset($row->title) ? $row : ($row->item ?? null);
    $purchaseId = $row->id ?? null;
    @endphp

    @if ($item)
    <div class="mypage-item">

      @if ($currentTab === 'transaction')
      <a href="{{ route('transaction.chat', $purchaseId) }}" class="item-link">
        @else
        <a href="{{ route('items.show', $item->id) }}" class="item-link">
          @endif

          <div class="item-thumb">

            @if ($currentTab === 'transaction' && isset($row->unread_count) && $row->unread_count > 0)
            <div class="item-badge">{{ $row->unread_count }}</div>
            @endif

            <img src="{{ $item->image_url }}" alt="{{ $item->title }}" class="item-img">
          </div>

          <div class="product-info">
            <div class="product-name">{{ $item->title }}</div>
            <div class="product-price">¥{{ number_format($item->price) }}</div>
          </div>

        </a>

    </div>
    @endif

    @empty
    <div class="muted">
      @if ($currentTab === 'buy')
      購入した商品はありません。
      @elseif($currentTab === 'transaction')
      取引中の商品はありません。
      @else
      まだ出品はありません。
      @endif
    </div>
    @endforelse

  </div>
</div>

@endsection