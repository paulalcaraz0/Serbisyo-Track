<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title inertia>{{ config('app.name', 'SerbisyoTrack') }}</title>
        <meta name="theme-color" content="#14594f">
        <link rel="icon" href="/branding/serbisyo-track-icon.png" type="image/png">
        <link rel="apple-touch-icon" href="/branding/serbisyo-track-icon.png">

        @routes
        @viteReactRefresh
        @vite(['resources/js/app.tsx', "resources/js/pages/{$page['component']}.tsx"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
