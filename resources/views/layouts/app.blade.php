<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Enterprise Portal</title>

    <!-- Tailwind CSS (via Vite) -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
        <script>
            tailwind.config = {
                darkMode: 'class',
            }
        </script>
    @endif
    @livewireStyles
</head>
<body class="antialiased bg-gray-50 text-gray-900 dark:bg-gray-900 dark:text-gray-100 transition-colors duration-200">
    {{ $slot }}
    @livewireScripts
</body>
</html>
