<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title inertia>{{ config('app.name', 'SerbisyoTrack') }}</title>
        <meta name="theme-color" content="#14594f">
        <link rel="icon" href="/branding/serbisyo-track-icon.png" type="image/png">
        <link rel="apple-touch-icon" href="/branding/serbisyo-track-icon.png">

        <script>
            (() => {
                const savedAppearance = localStorage.getItem('appearance');
                const appearance = ['light', 'dark', 'system'].includes(savedAppearance) ? savedAppearance : 'system';
                const isDark = appearance === 'dark' || (appearance === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);

                document.documentElement.classList.toggle('dark', isDark);
                document.documentElement.style.colorScheme = isDark ? 'dark' : 'light';
                document.querySelector('meta[name="theme-color"]').setAttribute('content', isDark ? '#0f1a18' : '#14594f');
            })();
        </script>

        @routes
        @viteReactRefresh
        @vite(['resources/js/app.tsx', "resources/js/pages/{$page['component']}.tsx"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
