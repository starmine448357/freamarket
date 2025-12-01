@extends('layouts.app')
@section('title','マイページ')

@section('page_css')
<link rel="stylesheet" href="{{ asset('css/mypage.css') }}">
@endsection

@section('content')
<div class="mypage">

  {{-- =========================
          プロフィール
    ========================== --}}
  <div class="mypage__header">
    <img
      src="{{ auth()->user()->profile_image_path
                    ? asset('storage/'.auth()->user()->profile_image_path)
                    : asset('images/default-avatar.png') }}"
      alt=""
      class="avatar avatar--lg"
      style="width:120px;height:120px;border-radius:50%;object-fit:cover">

    <div class="mypage__info">
      <h1 class="mypage__name">{{ auth()->user()->name }}</h1>
      <a href="{{ route('mypage.profile.edit') }}" class="btn btn--outline">
        プロフィールを編集
      </a>
    </div>
  </div>

  @php
  // ★ デフォルトは出品した商品タブ
  $currentTab = request('tab', 'sell');

  // ★ タブ別リスト（Controller で渡している想定）
  if ($currentTab === 'buy') {
  $list = $purchasedItems ?? [];
  } elseif ($currentTab === 'transaction') {
  $list = $transactionItems ?? [];
  } else {
  $list = $sellingItems ?? [];
  }
  @endphp


  {{-- =========================
          タブ（出品 / 購入 / 取引中）
    ========================== --}}
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
    </a>

  </div>


  {{-- =========================
          商品一覧（タブごと）
    ========================== --}}
  <div class="mypage__items">

    @forelse ($list as $row)

    @php
    // ★ 出品一覧 → row は Item そのもの
    // ★ 購入一覧 → row は Purchase モデル（item リレーション）
    // ★ 取引中も Purchase モデル
    $item = isset($row->title) ? $row : ($row->item ?? null);

    // 取引中タブ用
    $purchaseId = $row->id ?? null;
    @endphp

    @if ($item)
    <div class="mypage-item">

      {{-- ===============================
                        UI：カード全体クリック
                  =============================== --}}

      @if ($currentTab === 'transaction')
      {{-- ◆ 取引中：チャットへ遷移 --}}
      <a href="{{ route('transaction.chat', $purchaseId) }}" class="item-link">

        @else
        {{-- ◆ 出品 or 購入：商品詳細へ遷移 --}}
        <a href="{{ route('items.show', $item->id) }}" class="item-link">
          @endif

          <div class="item-thumb">
            <img src="{{ $item->image_url }}"
              alt="{{ $item->title }}"
              class="item-img">
          </div>

          <div class="product-info">
            <div class="product-name">{{ $item->title }}</div>
            <div class="product-price">¥{{ number_format($item->price) }}</div>
          </div>

        </a>

    </div>
    @endif

    @empty

    {{-- ===============================
                    リストが空の場合
              =============================== --}}
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

  </div>{{-- .mypage__items --}}

</div>{{-- .mypage --}}
@endsection