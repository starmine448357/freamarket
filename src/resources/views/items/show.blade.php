@extends('layouts.app')
@section('title', $item->title.' | 商品詳細')
@section('page_css')<link rel="stylesheet" href="{{ asset('css/items-detail.css') }}">@endsection

@section('content')
<div class="detail">
  <div class="detail__left">
<img class="thumb thumb--lg" src="{{ $item->image_url }}" alt="">
  </div>

  <div class="detail__right">
    <h1 class="detail__title">{{ $item->title }}</h1>
    <div class="muted">{{ $item->brand ?: '—' }}</div>
    <div class="price price--lg mt-xs">¥{{ number_format($item->price) }} <span class="muted">（税込）</span></div>

    <div class="actions mt-sm">
      @auth
        @if($item->status==='selling' && auth()->id() !== $item->user_id)
          <a class="btn btn--primary" href="{{ route('purchases.create',$item) }}">購入手続きへ</a>
        @endif
        @if(auth()->id() === $item->user_id)
          <a class="btn btn--outline link" href="{{ route('items.edit',$item) }}">編集</a>
          <form class="inline" method="POST" action="{{ route('items.destroy', $item) }}" onsubmit="return confirm('削除しますか？')">
            @csrf @method('DELETE')
            <button class="btn btn--danger" type="submit">削除</button>
          </form>
        @endif
      @endauth
    </div>

    <div class="section">
      <div class="label label--bold">商品説明</div>
      <p class="desc">{{ $item->description }}</p>
    </div>

    <div class="section">
      <div class="label label--bold">商品の情報</div>
      <div class="label">カテゴリー</div>
      <div class="chips">
        @foreach($item->categories as $cat)
          <span class="chip">{{ $cat->name }}</span>
        @endforeach
      </div>
      <div class="row mt-xs">
        <div class="label">商品の状態</div>
        <div class="ml-sm">{{ ['new'=>'新品','like_new'=>'未使用に近い','used'=>'中古'][$item->condition] ?? '—' }}</div>
      </div>
    </div>

    <div class="section">
      <div class="label label--bold">コメント（{{ $item->comments->count() }}）</div>
      <div class="comments">
        @forelse($item->comments as $c)
          <div class="comment">
            <div class="avatar avatar--sm"></div>
            <div>
              <div class="muted">{{ $c->user->name }} / {{ $c->created_at->format('Y-m-d H:i') }}</div>
              <div class="comment__text">{{ $c->content }}</div>
            </div>
          </div>
        @empty
          <div class="muted">コメントはまだありません。</div>
        @endforelse
      </div>

      @auth
      <form class="card card--pad form mt-sm" method="POST" action="{{ route('comments.store',$item) }}">
        @csrf
        <div class="field">
          <label class="label">商品のコメント</label>
          <textarea class="textarea" name="content" rows="4">{{ old('content') }}</textarea>
          @error('content')<div class="error">{{ $message }}</div>@enderror
        </div>
        <button class="btn btn--primary" type="submit">コメントを送信する</button>
      </form>
      @endauth
    </div>
  </div>
</div>
@endsection
