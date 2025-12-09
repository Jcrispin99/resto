<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="theme-color" content="#2563eb">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    
    <title>POS - {{ config('app.name') }}</title>

    <!-- Vite POS -->
    @vite(['resources/js/pos/pos.ts'])
</head>
<body class="antialiased">
    <div id="pos-app"></div>
    
    <!-- Config del servidor -->
    <script>
        window.APP_CONFIG = {
            apiUrl: '{{ url("/api") }}',
            appUrl: '{{ url("/") }}',
            user: @json(auth()->check() ? auth()->user() : null),
        };
    </script>
</body>
</html>
