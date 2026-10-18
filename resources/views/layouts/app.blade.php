<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('title') — WinNest</title>
  <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
<header class="site-header">
  <div class="container">
    <a class="brand" href="{{ route('home') }}">
      <img src="{{ asset('images/koopman-logo.png') }}" alt="Koopman loft logo" width="58" height="80">
      <span>WinNest</span>
    </a>
    <nav class="nav-links" aria-label="Main">
      <a href="{{ route('home') }}" @if(request()->routeIs('home')) aria-current="page" @endif>Home</a>
      <a href="{{ route('rankings') }}" @if(request()->routeIs('rankings')) aria-current="page" @endif>Pair Rankings</a>
      <a href="{{ route('results') }}" @if(request()->routeIs('results')) aria-current="page" @endif>Race Results</a>
      <a href="{{ route('pairs') }}" @if(request()->routeIs('pairs')) aria-current="page" @endif>Breeding Pairs</a>
    </nav>
  </div>
</header>

@yield('hero')

<main class="section container">
  @yield('content')
</main>

<footer class="site-footer">
  <div class="container">&copy; 2026 WinNest Racing System. All rights reserved.</div>
</footer>

@stack('scripts')
</body>
</html>