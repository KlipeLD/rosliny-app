<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="utf-8">
    <title>@yield('title', 'Rośliny')</title>

    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- CSRF dla formularzy --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Style --}}
    {{-- <link rel="stylesheet" href="{{ asset('css/app.css') }}"> --}}
    <link href="{{ asset('bootstrap-5.0.2/css/bootstrap.min.css') }}" rel="stylesheet">
    <script src="{{ asset('/js/other/jquery.min.js') }}"></script>
    <script src="{{ asset('/js/plants.js') }}"></script>
    @vite('resources/scss/app.scss')
</head>
<body>

<header style="padding:15px;border-bottom:1px solid #ddd;">
    <a href="{{ route('plants.index') }}">Rośliny</a>
</header>

<main style="padding:20px;">
    @if(session('success'))
        <div style="background:#e6ffea;padding:10px;margin-bottom:10px;">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div style="background:#ffe6e6;padding:10px;margin-bottom:10px;">
            {{ session('error') }}
        </div>
    @endif

    @yield('content')
</main>

@include('layouts.footer')


</body>
</html>
