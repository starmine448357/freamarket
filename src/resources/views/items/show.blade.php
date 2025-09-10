@extends('layouts.app')
@section('title', $item->title.' | 商品詳細')
@section('page_css')
@php
  $css = public_path('css/items-detail.css');
  $ver = file_exists($css) ? filemtime($css) : time();
@endphp
<link rel="stylesheet" href="{{ asset('css/items-detail.css') }}?v={{ $ver }}">
@endsection

@section('content')
<div class="detail">
  {{-- 左：画像 --}}
  <div class="detail__left">
    <img
      class="thumb thumb--lg"
      src="{{ $item->image_url }}"
      alt="{{ $item->title }}"
      decoding="async"
    >
  </div>

  {{-- 右：情報 --}}
  <div class="detail__right">
    <h1 class="detail__title">{{ $item->title }}</h1>
    <div class="muted">{{ $item->brand ?: '—' }}</div>

    {{-- 価格 --}}
    <div class="price price--lg mt-xs">
      ¥{{ number_format($item->price) }} <span class="muted">（税込）</span>
    </div>

    {{-- ☆いいね（トグル）＋ 💬コメント数 --}}
    @php
      // 将来的には controller 側で withCount(['likes','comments']) を推奨
      $liked         = auth()->check() ? $item->isLikedBy(auth()->user()) : false;
      $likesCount    = $item->likes_count    ?? $item->likes()->count();
      $commentsCount = $item->comments_count ?? $item->comments()->count();
    @endphp
    <div class="meta meta--interactive mt-xs" aria-label="商品メタ情報">
      <div class="meta__group">
        @auth
          @if($liked)
            <form method="POST" action="{{ route('likes.destroy', $item) }}" class="inline">
              @csrf @method('DELETE')
              <button type="submit" class="star-btn is-liked" aria-pressed="true" title="いいねを解除">
                <svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true">
                  <path d="M12 17.27 18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"></path>
                </svg>
                <span class="count">{{ $likesCount }}</span>
              </button>
            </form>
          @else
            <form method="POST" action="{{ route('likes.store', $item) }}" class="inline">
              @csrf
              <button type="submit" class="star-btn" aria-pressed="false" title="いいねする">
                <svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true">
                  <path fill="none" stroke="currentColor" stroke-width="2"
                        d="M12 17.27 18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/>
                </svg>
                <span class="count">{{ $likesCount }}</span>
              </button>
            </form>
          @endif
        @else
          <a class="star-btn" href="{{ route('login') }}" title="ログインしていいね">
            <svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true">
              <path fill="none" stroke="currentColor" stroke-width="2"
                    d="M12 17.27 18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/>
            </svg>
            <span class="count">{{ $likesCount }}</span>
          </a>
        @endauth

        <a class="meta-link" href="#comments" title="コメントへ移動">
          <span class="bubble">💬</span>
          <span class="count">{{ $commentsCount }}</span>
        </a>
      </div>
    </div>

    {{-- 購入ボタン／状態表示 --}}
    <div class="mt-xs">
      @if($item->is_sold)
        <div class="badge badge--sold">SOLD</div>

      @elseif(auth()->check() && auth()->id() === $item->user_id)
        <button class="btn btn--disabled" type="button" disabled>あなたの商品です</button>

      @elseif(auth()->check())
        <a class="btn btn--primary" href="{{ route('purchases.create', $item) }}">
          購入手続きへ
        </a>

      @else
        <a class="btn btn--primary" href="{{ route('login') }}">購入手続きへ</a>
      @endif
    </div>

    {{-- 商品説明 --}}
    <div class="section">
      <div class="label label--bold">商品説明</div>
      <p class="desc">{{ $item->description }}</p>
    </div>

    {{-- 商品の情報 --}}
    <div class="section">
      <div class="label label--bold">商品の情報</div>

      <div class="label">カテゴリー</div>
      <div class="chips">
        @forelse($item->categories as $cat)
          <span class="chip">{{ $cat->name }}</span>
        @empty
          <span class="muted">—</span>
        @endforelse
      </div>

      <div class="row mt-xs">
        <div class="label">商品の状態</div>
        <div class="ml-sm">
          {{ ['new'=>'新品','like_new'=>'未使用に近い','used'=>'中古'][$item->condition] ?? '—' }}
        </div>
      </div>
    </div>

    {{-- コメント --}}
    <div class="section" id="comments" aria-labelledby="comments-title">
      <div class="label label--bold" id="comments-title">コメント（{{ $commentsCount }}）</div>

      {{-- コメント一覧（0件ならリスト自体を表示しない） --}}
      @if($commentsCount > 0)
        <ul class="comment-list">
          @foreach($item->comments as $comment)
            <li class="comment">
              <div class="comment__header">
                <img
                  class="comment__avatar"
                  src="{{ $comment->user->avatar_url ?? asset('images/user-gray.png') }}"
                  alt=""
                >
                <span class="comment__name">{{ $comment->user->name }}</span>
              </div>
              <p class="comment__body">{{ $comment->content }}</p>
              {{-- ↑ カラム名が body や text なら置き換え --}}
            </li>
          @endforeach
        </ul>
      @endif

      {{-- フォーム --}}
      <form id="comment-form" class="form mt-sm" method="POST" action="{{ route('comments.store', $item) }}">
        @csrf
        <div class="field">
          <label for="comment-content" class="label label--bold">商品のコメント</label>
          <textarea
            id="comment-content"
            class="textarea"
            name="content"
            rows="4"
            placeholder="コメントを入力してください"
            maxlength="255"
          >{{ old('content') }}</textarea>
          @error('content')<div class="error">{{ $message }}</div>@enderror
        </div>

        @auth
          <button class="btn btn--primary" type="submit">コメントを送信する</button>
        @else
          <button class="btn btn--primary" type="submit" formmethod="GET" formaction="{{ route('login') }}">
            コメントを送信する
          </button>
        @endauth
      </form>
    </div>
  </div>
</div>
@endsection
