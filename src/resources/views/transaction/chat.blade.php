@extends('layouts.chat')
@section('title', '取引チャット')

@section('css')
<link rel="stylesheet" href="{{ asset('css/transaction-chat.css') }}">
@endsection

@section('content')

<div class="transaction-wrapper">

    @php
    $me = Auth::user();
    $isSeller = ($purchase->item->user_id === $me->id);
    $editingId = request('editing_id');
    @endphp

    {{-- =======================
         左サイドバー
    ======================== --}}
    <aside class="sidebar">
        <h2 class="sidebar-title">その他の取引</h2>

        @foreach($relatedPurchases as $p)
        <a href="{{ route('transaction.chat', $p->id) }}"
            class="sidebar-item {{ $p->id == $purchase->id ? 'active' : '' }}">
            <span class="sidebar-item-name">{{ $p->item->title }}</span>
        </a>
        @endforeach
    </aside>

    {{-- =======================
         メインエリア
    ======================== --}}
    <div class="chat-main">

        {{-- ▼ 取引相手の取得 --}}
        @php
        $partner = ($purchase->buyer_id === $me->id)
        ? $purchase->item->user
        : $purchase->buyer;
        @endphp

        {{-- ▼ ヘッダー --}}
        <div class="chat-header">
            <div class="header-left">

                <img src="{{ $partner->profile_image_path
                            ? asset('storage/' . $partner->profile_image_path)
                            : asset('images/default-avatar.png') }}"
                    class="user-icon">

                <h1 class="chat-title">「{{ $partner->name }}」さんとの取引画面</h1>

            </div>

            {{-- 購入者だけ「取引完了ボタン」表示 --}}
            @if ($purchase->buyer_id === $me->id)
            <button class="finish-btn" id="open-review-modal">取引を完了する</button>
            @endif
        </div>

        <div class="full-divider"></div>

        {{-- =======================
             商品情報
        ======================== --}}
        <div class="item-info">

            <div class="item-image-box">
                <img src="{{ $purchase->item->image_url }}" class="item-image">
            </div>

            <div class="item-info-text">
                <h2 class="item-name">{{ $purchase->item->title }}</h2>
                <p class="item-price">¥{{ number_format($purchase->item->price) }}</p>
            </div>

        </div>

        <div class="full-divider"></div>

        {{-- =======================
             メッセージ一覧
        ======================== --}}
        <div class="messages-area">

            @foreach($messages as $msg)

            {{-- -------------------------------------
                     相手のメッセージ（左）
                -------------------------------------- --}}
            @if ($msg->user_id !== $me->id)

            <div class="message-row left">

                <div class="message-header">
                    <img src="{{ $msg->user->profile_image_path
                                        ? asset('storage/' . $msg->user->profile_image_path)
                                        : asset('images/default-avatar.png') }}"
                        class="user-icon">
                    <p class="user-name">{{ $msg->user->name }}</p>
                </div>

                <div class="message-body">
                    <div class="bubble bubble-left">

                        @if ($msg->message)
                        <div>{{ $msg->message }}</div>
                        @endif

                        @if ($msg->image_path)
                        <img src="{{ asset('storage/' . $msg->image_path) }}" class="chat-image">
                        @endif

                    </div>
                </div>

            </div>

            {{-- -------------------------------------
                     自分のメッセージ（右）
                -------------------------------------- --}}
            @else

            <div class="message-row right">

                <div class="message-header my-header">
                    <img src="{{ $msg->user->profile_image_path
                                        ? asset('storage/' . $msg->user->profile_image_path)
                                        : asset('images/default-avatar.png') }}"
                        class="user-icon">

                    <p class="user-name">{{ $msg->user->name }}</p>
                </div>

                <div class="message-body my-body">

                    {{-- ▼ 編集モード --}}
                    @if ($editingId == $msg->id)

                    <form action="{{ route('transaction.chat.update', [$purchase->id, $msg->id]) }}"
                        method="POST"
                        class="message-edit-form">
                        @csrf
                        @method('PUT')

                        <textarea name="message"
                            class="edit-textarea">{{ old('message', $msg->message) }}</textarea>

                        <input type="hidden" name="editing_id" value="{{ $msg->id }}">

                        <div class="edit-actions">
                            <button type="submit" class="edit-save">保存</button>
                            <a href="{{ route('transaction.chat', $purchase->id) }}"
                                class="edit-cancel-btn">キャンセル</a>
                        </div>
                    </form>

                    {{-- ▼ 通常表示 --}}
                    @else

                    <div class="bubble bubble-right">

                        @if ($msg->message)
                        <div>{{ $msg->message }}</div>
                        @endif

                        @if ($msg->image_path)
                        <img src="{{ asset('storage/' . $msg->image_path) }}" class="chat-image">
                        @endif

                    </div>

                    <div class="msg-actions">

                        {{-- 編集ボタン --}}
                        <a href="{{ route('transaction.chat', ['purchaseId' => $purchase->id, 'editing_id' => $msg->id]) }}"
                            class="msg-edit">
                            編集
                        </a>

                        {{-- 削除ボタン --}}
                        <form action="{{ route('transaction.chat.delete', [$purchase->id, $msg->id]) }}"
                            method="POST"
                            class="delete-form">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="msg-delete">削除</button>
                        </form>

                    </div>

                    @endif

                </div>
            </div>

            @endif

            @endforeach

        </div>

        {{-- =======================
             送信フォーム
        ======================== --}}
        @if ($errors->any())
        <div class="chat-error-box">
            {{ $errors->first() }}
        </div>
        @endif

        <form class="send-form"
            action="{{ route('transaction.chat.store', $purchase->id) }}"
            method="POST"
            enctype="multipart/form-data">
            @csrf

            <input type="text"
                name="message"
                class="message-input"
                placeholder="取引メッセージを記入してください"
                value="{{ old('message') }}">

            <p id="selected-image-name" class="selected-image-name"></p>

            <input id="chat-image"
                type="file"
                name="image"
                accept="image/png,image/jpeg"
                class="image-input">

            <label for="chat-image" class="image-add-btn">画像を追加</label>

            <button type="submit" class="send-btn">
                <img src="{{ asset('images/paper-plane.png') }}"
                    class="send-icon"
                    alt="送信">
            </button>
        </form>

        {{-- =======================
             取引完了モーダル
        ======================== --}}
        <div id="review-modal" class="review-modal">
            <div class="review-modal-content">

                <h2 class="modal-title">取引が完了しました。</h2>
                <div class="modal-divider"></div>

                <p class="modal-sub">今回の取引相手はどうでしたか？</p>

                <form action="{{ route('review.store', $purchase->id) }}" method="POST">
                    @csrf

                    <div class="modal-stars">
                        <input type="radio" id="star5" name="rating" value="5"><label for="star5">★</label>
                        <input type="radio" id="star4" name="rating" value="4"><label for="star4">★</label>
                        <input type="radio" id="star3" name="rating" value="3"><label for="star3">★</label>
                        <input type="radio" id="star2" name="rating" value="2"><label for="star2">★</label>
                        <input type="radio" id="star1" name="rating" value="1"><label for="star1">★</label>
                    </div>

                    <div class="modal-divider"></div>

                    <div class="modal-buttons single">
                        <button type="submit" class="modal-submit-btn">送信する</button>
                    </div>

                </form>
            </div>
        </div>

        {{-- =======================
             JS：スクロール保持
        ======================== --}}
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const area = document.querySelector('.chat-main');
                const saved = sessionStorage.getItem('scrollPos');

                if (saved && area) {
                    area.scrollTop = parseInt(saved);
                    sessionStorage.removeItem('scrollPos');
                }

                document.querySelectorAll('form').forEach(form => {
                    form.addEventListener('submit', function() {
                        if (area) {
                            sessionStorage.setItem('scrollPos', area.scrollTop);
                        }
                    });
                });
            });
        </script>

        {{-- 画像名表示 --}}
        <script>
            document.getElementById('chat-image').addEventListener('change', function() {
                const file = this.files[0];
                const target = document.getElementById('selected-image-name');
                target.textContent = file ? '選択された画像：' + file.name : '';
            });
        </script>

        {{-- モーダル開閉 --}}
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const modal = document.getElementById('review-modal');
                const openBtn = document.getElementById('open-review-modal');

                if (openBtn) {
                    openBtn.addEventListener('click', () => {
                        modal.style.display = 'flex';
                    });
                }
            });
        </script>

        {{-- 出品者側：購入者レビュー済みなら強制表示 --}}
        @if($isSeller && $purchase->status === 'buyer_reviewed')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                document.getElementById('review-modal').style.display = 'flex';
            });
        </script>
        @endif

        {{-- 入力内容ドラフト保持（localStorage） --}}
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const input = document.querySelector('.message-input');
                if (!input) return;

                const KEY = "chat_draft_{{ $purchase->id }}";

                const saved = localStorage.getItem(KEY);
                if (saved !== null) {
                    input.value = saved;
                }

                input.addEventListener('input', () => {
                    localStorage.setItem(KEY, input.value);
                });

                const form = input.closest('form');
                if (form) {
                    form.addEventListener('submit', () => {
                        localStorage.removeItem(KEY);
                    });
                }
            });
        </script>

    </div>
</div>

@endsection