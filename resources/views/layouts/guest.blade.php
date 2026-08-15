<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ isset($title) && trim((string) $title) !== '' ? trim((string) $title).' — '.$identity['application_name'] : $identity['application_name'] }}</title>
    <link rel="icon" href="{{ $identity['favicon_url'] }}">

    <!-- Inline Anti-Flicker Theme Script -->
    <script>
        (function() {
            const key = 'sabira_theme_preference';
            const stored = localStorage.getItem(key) || 'system';
            const isDark = stored === 'dark' || (stored === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
            if (isDark) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
            document.documentElement.setAttribute('data-theme', stored);
        })();
    </script>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full antialiased font-sans bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-slate-100">
    {{ $slot }}
</body>
</html>
