<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title') - {{ config('app.name', 'MomoOpti') }}</title>

    <!-- PWA Manifest -->
{!! PwaKit::head() !!}


    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="font-sans antialiased bg-gray-50 text-gray-900">
    <div class="min-h-screen flex flex-col">
        @include('components.navigation')

        <!-- Page Content -->
        <main class="flex-grow">
            @if (session('success') || session('error'))
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
                    @include('components.alert')
                </div>
            @endif

            @hasSection('content')
                @yield('content')
            @else
                {{ $slot }}
            @endif
        </main>

        @include('components.footer')
    </div>

    @livewireScripts

    <!-- Service Worker Registration -->
{!! PwaKit::scripts() !!}
</body>
</html>
