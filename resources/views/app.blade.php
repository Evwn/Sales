<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="light">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <title inertia>{{ config('app.name', 'Sales Management System') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/js/app.js', 'resources/css/app.css'])
    @inertiaHead

    <script>
        // Always force light mode
        document.documentElement.classList.remove('dark');
    </script>

    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#2563eb">
    <link rel="apple-touch-icon" href="/icons/icon-192x192.png">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <script>
      if ('serviceWorker' in navigator) {
        window.addEventListener('load', function() {
          navigator.serviceWorker.register('/service-worker.js');
        });
      }
    </script>
  </head>
  <body class="min-h-screen bg-background font-sans antialiased text-foreground transition-colors duration-200">
    @inertia
    <script src="https://checkout.flutterwave.com/v3.js"></script>
  </body>
</html>