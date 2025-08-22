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
      <a href="{{ route('items.index') }}" class="brand link">COACHTECH</a>
    </div>
    <div class="header__center">
      <form action="{{ route('items.index') }}" method="GET" class="search">
        <input class="input search__input" type="text" name="q" value="{{ request('q') }}" placeholder="なにをお探しですか？">
      </form>
    </div>
  <nav class="header__right nav">
    @auth
      <a class="link nav__link" href="{{ route('items.index', ['tab'=>'mylist']) }}">マイリスト</a>
      <a class="link nav__link" href="{{ route('mypage.index') }}">マイページ</a> {{-- ←ボタン扱いでもOK --}}
      <a class="btn btn--outline link" href="{{ route('items.create') }}">出品</a>
      <a class="link nav__link" href="{{ route('logout') }}"
        onclick="event.preventDefault(); document.getElementById('logout-form').submit();">ログアウト</a>
      <form id="logout-form" class="hidden" method="POST" action="{{ route('logout') }}">@csrf</form>
    @else
      <a class="link nav__link" href="{{ route('login') }}">ログイン</a>
      <a class="link nav__link" href="{{ route('register') }}">新規登録</a>
      <a class="btn btn--outline link" href="{{ route('login') }}">出品</a>
    @endauth
  </nav>
  </header>

  <main class="container">
    @if (session('success')) <div class="flash">{{ session('success') }}</div> @endif
    @yield('content')
  </main>
</body>
</html>
