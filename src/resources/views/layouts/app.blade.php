<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>@yield('title', 'Freamarket')</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="{{ asset('css/app.css') }}">
  @yield('page_css')
</head>
<body class="body">
<header class="header">
  <div class="header__left">
    <a href="{{ route('items.index') }}" class="brand">
      <img src="{{ asset('images/logo.svg') }}" alt="Freamarket" class="site-logo">
    </a>
  </div>

  <div class="header__center">
    <form action="{{ route('items.index') }}" method="GET" class="search">
      <input class="input search__input" type="text" name="q" value="{{ request('q') }}" placeholder="なにをお探しですか？" aria-label="検索">
    </form>
  </div>

  <nav class="header__right nav">
    @auth
      {{-- 右側：ログアウト → マイページ → 出品 --}}
      <a class="link nav__link" href="{{ route('logout') }}"
         onclick="event.preventDefault(); document.getElementById('logout-form').submit();">ログアウト</a>
      <form id="logout-form" class="hidden" method="POST" action="{{ route('logout') }}">@csrf</form>

      <a class="link nav__link" href="{{ route('mypage') }}">マイページ</a>
      <a class="btn btn--sell link" href="{{ route('items.create') }}">出品</a>
    @else
      {{-- 未ログイン時：ログイン → マイページ → 出品（ログインへ誘導） --}}
      <a class="link nav__link" href="{{ route('login') }}">ログイン</a>
      <a class="link nav__link" href="{{ route('mypage') }}">マイページ</a>
      <a class="btn btn--sell link" href="{{ route('login') }}">出品</a>
    @endauth
  </nav>
</header>

<main class="container">
  @if (session('success')) <div class="flash">{{ session('success') }}</div> @endif
  @yield('content')
</main>
</body>
</html>
