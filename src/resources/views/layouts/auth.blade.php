<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>@yield('title', 'Freamarket')</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
  @yield('page_css')
</head>
<body class="auth-body">
  <header class="auth-header">
    <a href="{{ route('items.index') }}" class="auth-header__brand">
      <img src="{{ asset('images/logo.svg') }}" alt="Freamarket" class="auth-header__logo">
    </a>
  </header>

  <main class="auth-container">
    @if (session('success')) <div class="auth-flash">{{ session('success') }}</div> @endif
    @yield('content')
  </main>
</body>
</html>
