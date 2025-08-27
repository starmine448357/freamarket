@extends('layouts.app')
@section('title','マイページ')
@section('page_css')
<link rel="stylesheet" href="{{ asset('css/mypage.css') }}">
@endsection

@section('content')
<div class="mypage">
  <div class="mypage__header">
    <div class="avatar avatar--lg"></div>
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
    // selling のときは Item のコレクション
    // purchased のときは Purchase のコレクション（中に item がある）
    $list = $currentTab==='selling' ? $sellingItems : $purchasedItems;
  @endphp

  @forelse($list as $row)
    @php
      // selling: $row は Item
      // purchased: $row は Purchase なので $row->item を見る
      $entity = isset($row->title) ? $row : ($row->item ?? null);
    @endphp

    @if($entity)
      <div class="item-card">
        <img src="{{ $entity->image_path ? asset('storage/'.$entity->image_path) : asset('img/placeholder.png') }}"
             alt="{{ $entity->title }}">
        <div>{{ $entity->title }}</div>
      </div>
    @endif
  @empty
    <div class="muted">
      {{ $currentTab==='selling' ? 'まだ出品はありません。' : 'まだ購入はありません。' }}
    </div>
  @endforelse
</div>
</div>
@endsection
