<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title')</title>

    {{-- チャット画面専用CSS --}}
    <link rel="stylesheet" href="{{ asset('css/transaction-chat.css') }}">

    @yield('css')
</head>

<body>

    {{-- ============================
         チャット画面専用ヘッダー
       ============================ --}}
    <header class="chat-header-only">
        <a href="{{ route('items.index') }}" class="chat-logo">
            <img src="{{ asset('images/logo.svg') }}" alt="COACHTECH">
        </a>
    </header>

    {{-- メインコンテンツ --}}
    <main>
        @yield('content')
    </main>

</body>

</html>