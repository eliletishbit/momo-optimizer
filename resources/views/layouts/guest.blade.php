<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title') - {{ config('app.name', 'MomoOpti') }}</title>


    <!-- PWA Manifest -->
    <!-- PWA Manifest -->
{!! PwaKit::head() !!}

<!-- PWA Theme color -->
<meta name="theme-color" content="#4F46E5">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-[#f8fafc] text-slate-900 overflow-x-hidden">
    <div class="min-h-screen relative flex items-center justify-center p-4 sm:p-6 lg:p-8">
        <!-- Background Decorative Elements (Same as Welcome Page) -->
        <div class="fixed inset-0 overflow-hidden pointer-events-none -z-10">
            <div class="absolute -top-[10%] -left-[10%] w-[40%] h-[40%] bg-indigo-200/40 rounded-full mix-blend-multiply filter blur-[80px] animate-pulse"></div>
            <div class="absolute top-[20%] -right-[5%] w-[35%] h-[35%] bg-emerald-200/30 rounded-full mix-blend-multiply filter blur-[80px] animate-pulse animation-delay-2000"></div>
            <div class="absolute -bottom-[10%] left-[20%] w-[40%] h-[40%] bg-pink-100/40 rounded-full mix-blend-multiply filter blur-[80px] animate-pulse animation-delay-4000"></div>
        </div>

        <div class="w-full max-w-[1100px] grid grid-cols-1 lg:grid-cols-2 bg-white/70 backdrop-blur-2xl rounded-[3rem] shadow-[0_20px_50px_rgba(0,0,0,0.05)] border border-white/50 overflow-hidden">
            
            <!-- Left Side: Decorative Info (Visible on Desktop) -->
            <div class="hidden lg:flex flex-col justify-between p-12 bg-gradient-to-br from-indigo-600 to-indigo-800 text-white relative overflow-hidden">
                <div class="relative z-10">
                    <a href="/" class="text-3xl font-extrabold tracking-tight">
                        MomoOpti<span class="text-emerald-400">.</span>
                    </a>
                    <h2 class="mt-16 text-4xl font-bold leading-tight">
                        L'intelligence au service de votre <span class="text-emerald-400">portefeuille.</span>
                    </h2>
                    <p class="mt-6 text-indigo-100 text-lg leading-relaxed max-w-sm">
                        Rejoignez plus de 10,000 utilisateurs qui optimisent leurs frais de transactions chaque jour.
                    </p>
                </div>

                <div class="relative z-10 space-y-6">
                    <div class="flex items-center space-x-4">
                        <div class="w-10 h-10 bg-white/10 backdrop-blur-md rounded-xl flex items-center justify-center">
                            <i class="fas fa-shield-halved text-emerald-400"></i>
                        </div>
                        <span class="text-sm font-medium">Sécurisé & Conforme</span>
                    </div>
                    <div class="flex items-center space-x-4">
                        <div class="w-10 h-10 bg-white/10 backdrop-blur-md rounded-xl flex items-center justify-center">
                            <i class="fas fa-bolt text-emerald-400"></i>
                        </div>
                        <span class="text-sm font-medium">Analyse instantanée</span>
                    </div>
                </div>

                <!-- Abstract decoration -->
                <div class="absolute -bottom-20 -right-20 w-64 h-64 bg-emerald-400/20 rounded-full blur-3xl"></div>
            </div>

            <!-- Right Side: The Form -->
            <div class="flex flex-col p-8 sm:p-12 lg:p-16 relative">
                <!-- Mobile Logo -->
                <div class="lg:hidden flex justify-center mb-10">
                    <a href="/" class="text-3xl font-extrabold tracking-tight text-indigo-600">
                        MomoOpti<span class="text-emerald-500">.</span>
                    </a>
                </div>
                
                <div class="flex-grow flex flex-col justify-center">
                    @yield('content')
                </div>

                <div class="mt-12 text-center">
                    <p class="text-[11px] text-slate-400 uppercase tracking-widest font-bold">
                        &copy; {{ date('Y') }} MomoOpti — Fintech Solutions
                    </p>
                </div>
            </div>
        </div>
    </div>

     @stack('scripts')


     <!-- Service Worker Registration -->
{!! PwaKit::scripts() !!}
</body>
</html>
