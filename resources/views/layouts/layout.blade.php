@php
    $isPaperMode = Auth::check() ? (bool)Auth::user()->alpaca_is_paper : true;
    $themeColor = $isPaperMode ? 'indigo' : 'emerald';
    $themeGlow = $isPaperMode ? 'from-indigo-500 via-purple-500 to-indigo-500 shadow-[0_0_15px_rgba(99,102,241,0.6)]' : 'from-emerald-500 via-teal-500 to-emerald-500 shadow-[0_0_15px_rgba(16,185,129,0.6)]';
    $headerBgClass = $isPaperMode 
        ? 'from-[#0d122b] via-[#1a1f4c] to-[#0f113a] border-indigo-500/45 shadow-indigo-500/15' 
        : 'from-[#061512] via-[#092921] to-[#051a14] border-emerald-500/45 shadow-emerald-500/15';
@endphp
<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'GoInvesting | Plataforma Financiera en Tiempo Real')</title>
    
    <!-- Meta SEO -->
    <meta name="description" content="@yield('meta_description', 'Sigue los mercados financieros globales con cotizaciones en tiempo real, gráficos interactivos de acciones, divisas, criptomonedas y materias primas.')">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind / Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- TradingView Lightweight Charts CDN -->
    <script src="https://unpkg.com/lightweight-charts@4/dist/lightweight-charts.standalone.production.js"></script>

    <!-- Custom Premium Styles -->
    <style>
        [x-cloak] {
            display: none !important;
        }

        body {
            font-family: 'Plus Jakarta Sans', 'Outfit', sans-serif;
            background-color: #070913;
            @if(!$isPaperMode)
                background-image: url('{{ asset('images/live_mode_bg.png') }}');
                background-size: cover;
                background-position: center;
                background-repeat: no-repeat;
                background-attachment: fixed;
            @else
                background-image: radial-gradient(circle at 50% -10%, rgba(99, 102, 241, 0.06) 0%, transparent 65%);
            @endif
            color: #f8fafc;
            overflow-x: hidden;
        }

        @if(!$isPaperMode)
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background: radial-gradient(circle at 50% 50%, rgba(6, 21, 18, 0.78) 0%, rgba(7, 9, 19, 0.97) 100%);
            z-index: -1;
            pointer-events: none;
        }
        @endif
        
        .glass-panel {
            background: rgba(15, 23, 42, 0.45);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.06);
        }

        .glass-panel-hover:hover {
            border-color: rgba(99, 102, 241, 0.25);
            background: rgba(15, 23, 42, 0.6);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .text-glow-green {
            text-shadow: 0 0 10px rgba(34, 197, 94, 0.2);
        }

        .text-glow-red {
            text-shadow: 0 0 10px rgba(239, 68, 68, 0.2);
        }

        @keyframes live-pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.6);
            }
            70% {
                box-shadow: 0 0 0 10px rgba(16, 185, 129, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(16, 185, 129, 0);
            }
        }
        .animate-live-glow {
            animation: live-pulse 2s infinite;
        }

        @keyframes paper-pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(99, 102, 241, 0.6);
            }
            70% {
                box-shadow: 0 0 0 10px rgba(99, 102, 241, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(99, 102, 241, 0);
            }
        }
        .animate-paper-glow {
            animation: paper-pulse 2.5s infinite;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #070913;
        }
        ::-webkit-scrollbar-thumb {
            background: #1e293b;
            border-radius: 3px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #4f46e5;
        }
        
        [x-cloak] {
            display: none !important;
        }
    </style>
</head>
<body class="min-h-screen flex flex-col antialiased selection:bg-indigo-500 selection:text-white">

    <!-- Thin top glowing mode indicator bar -->
    <div class="h-[3.5px] w-full bg-gradient-to-r {{ $themeGlow }} transition-all duration-300 relative z-50"></div>

    <!-- Main Navigation Header -->
    <header class="sticky top-0 z-40 w-full bg-gradient-to-r {{ $headerBgClass }} backdrop-blur-md border-b shadow-lg transition-all duration-300">
        <div class="max-w-7xl mx-auto px-4 lg:px-6 h-16 flex items-center justify-between gap-4">
            
            <!-- Logo & Mode Badge/Selector -->
            <div class="flex items-center gap-4 shrink-0">
                <a href="{{ route('home') }}" class="flex items-center gap-2.5 select-none group">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-{{ $themeColor }}-500 to-{{ $isPaperMode ? 'violet' : 'teal' }}-500 flex items-center justify-center shadow-lg shadow-{{ $themeColor }}-500/30 group-hover:scale-105 transition-all duration-300">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5 text-white">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.307a11.95 11.95 0 0 0 5.814-5.519l2.74-1.22m0 0-5.94-2.28m5.94 2.28-2.28 5.941" />
                        </svg>
                    </div>
                    <span class="font-extrabold text-xl tracking-tight flex items-center select-none">
                        <span class="bg-gradient-to-r from-white to-slate-200 bg-clip-text text-transparent">Go</span>
                        <span class="bg-gradient-to-r from-{{ $themeColor }}-300 to-{{ $isPaperMode ? 'violet' : 'teal' }}-400 bg-clip-text text-transparent">Investing</span>
                    </span>
                </a>
                
                @auth
                    <!-- Selector de Modo de Trading -->
                    <div class="flex items-center gap-0.5 p-0.5 bg-slate-950/90 border border-slate-800/80 rounded-xl shadow-xl select-none scale-90 sm:scale-100">
                        <form action="{{ route('portfolio.toggle-paper') }}" method="POST" class="m-0">
                            @csrf
                            <input type="hidden" name="mode" value="paper">
                            <button type="submit" 
                                    @if(Auth::user()->alpaca_is_paper) disabled @endif
                                    class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider transition-all duration-300 {{ Auth::user()->alpaca_is_paper ? 'bg-indigo-650 text-white shadow-md shadow-indigo-650/15 cursor-default animate-paper-glow' : 'text-slate-550 hover:text-slate-350 border border-transparent cursor-pointer' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ Auth::user()->alpaca_is_paper ? 'bg-indigo-400 shadow-sm shadow-indigo-400/50 animate-pulse' : 'bg-slate-700' }}"></span>
                                Demo
                            </button>
                        </form>
                        <form action="{{ route('portfolio.toggle-paper') }}" method="POST" class="m-0">
                            @csrf
                            <input type="hidden" name="mode" value="live">
                            <button type="submit" 
                                    @if(!Auth::user()->alpaca_is_paper) disabled @endif
                                    class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider transition-all duration-300 {{ !Auth::user()->alpaca_is_paper ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/15 cursor-default animate-live-glow' : 'text-slate-550 hover:text-slate-350 border border-transparent cursor-pointer' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ !Auth::user()->alpaca_is_paper ? 'bg-emerald-400 shadow-sm shadow-emerald-400/50 animate-pulse' : 'bg-slate-700' }}"></span>
                                Real
                            </button>
                        </form>
                    </div>
                @else
                    <span class="text-[9px] font-extrabold text-indigo-455 bg-indigo-950/20 px-2.5 py-1 rounded-lg border border-indigo-500/20 uppercase tracking-wider">
                        Modo Demo
                    </span>
                @endauth
            </div>

            <!-- Navigation Links -->
            <div x-data="{ activeSection: window.location.hash || '' }" 
                 x-on:hashchange.window="activeSection = window.location.hash"
                 x-on:section-change.window="activeSection = $event.detail"
                 class="hidden md:flex items-center gap-2.5 lg:gap-3.5 text-[11.5px] lg:text-[12px] font-semibold tracking-wide whitespace-nowrap h-16">
                
                <a href="{{ route('home') }}" 
                   class="transition-all duration-150 border-b-2 pb-1 shrink-0 mt-[2px] {{ Route::is('home') && empty($query) ? 'text-indigo-400 border-indigo-500 font-bold' : 'text-slate-300 hover:text-white border-transparent' }}">Mercados</a>

                @auth
                    <a href="{{ route('portfolio') }}" class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-xl text-[9.5px] font-black uppercase tracking-wider transition-all duration-200 hover:scale-[1.02] active:scale-[0.98] select-none shrink-0 {{ Route::is('portfolio') ? 'bg-indigo-650 text-white border border-indigo-400 shadow-md shadow-indigo-600/35' : 'bg-gradient-to-r from-indigo-500/20 to-violet-500/20 text-indigo-300 border border-indigo-500/40 hover:from-indigo-500/30 hover:to-violet-500/30 hover:text-white hover:border-indigo-400 shadow-md shadow-indigo-950/20' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3.5 h-3.5 text-indigo-400">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5h16.5M5.25 7.5h13.5m-12 3h10.5m-9 3h7.5m-6 3h4.5m-3.75 3h3" />
                        </svg>
                        Mi Portafolio
                    </a>
                    @php
                        $notConfigured = !Auth::user()->alpaca_key_id && !Auth::user()->alpaca_live_key_id;
                    @endphp
                    <a href="{{ route('getting-started') }}" class="transition-all duration-150 flex items-center gap-1 shrink-0 {{ $notConfigured ? 'px-2 py-1 rounded-lg text-[10.5px] bg-amber-500/10 text-amber-400 border border-amber-500/35 font-bold animate-pulse hover:bg-amber-500/20 hover:text-amber-300' : (Route::is('getting-started') ? 'text-indigo-400 border-b-2 border-indigo-500 pb-1 mt-[2px]' : 'text-slate-400 hover:text-white border-b-2 border-transparent pb-1 mt-[2px]') }}">
                        @if($notConfigured)
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3.5 h-3.5 text-amber-400">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                            </svg>
                        @endif
                        Cómo Empezar
                    </a>
                @endauth
                <a href="{{ Route::is('home') ? '#como-funcionamos' : route('home') . '#como-funcionamos' }}" 
                   x-on:click="activeSection = '#como-funcionamos'"
                   :class="activeSection === '#como-funcionamos' ? 'text-indigo-400 border-indigo-500' : 'text-slate-300 hover:text-white border-transparent'"
                   class="transition-all duration-150 border-b-2 pb-1 shrink-0 mt-[2px]">Cómo Funcionamos</a>
                <a href="{{ Route::is('home') ? '#quienes-somos' : route('home') . '#quienes-somos' }}" 
                   x-on:click="activeSection = '#quienes-somos'"
                   :class="activeSection === '#quienes-somos' ? 'text-indigo-400 border-indigo-500' : 'text-slate-300 hover:text-white border-transparent'"
                   class="transition-all duration-150 border-b-2 pb-1 shrink-0 mt-[2px]">Quiénes Somos</a>
                <a href="{{ Route::is('home') ? '#contacto' : route('home') . '#contacto' }}" 
                   x-on:click="activeSection = '#contacto'"
                   :class="activeSection === '#contacto' ? 'text-indigo-400 border-indigo-500' : 'text-slate-300 hover:text-white border-transparent'"
                   class="transition-all duration-150 border-b-2 pb-1 shrink-0 mt-[2px]">Contacto</a>
            </div>

            <!-- Auth Actions -->
            <div class="flex items-center gap-2.5 shrink-0">
                @auth
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" @click.outside="open = false" class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-slate-900 border border-slate-800 hover:border-slate-700 transition duration-200">
                            <div class="w-6 h-6 rounded-full bg-indigo-600 flex items-center justify-center text-xs font-bold text-white uppercase">
                                {{ substr(Auth::user()->name, 0, 1) }}
                            </div>
                            <span class="text-sm font-semibold text-slate-200 hidden sm:inline">{{ Auth::user()->name }}</span>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                            </svg>
                        </button>
                        <!-- Dropdown -->
                        <div x-show="open" class="absolute right-0 mt-2 w-48 bg-[#0d1222] border border-slate-800 rounded-xl shadow-2xl py-1 z-50" x-transition>
                            <a href="{{ route('profile.edit') }}" class="block px-4 py-2.5 text-sm text-slate-300 hover:bg-slate-900/60 hover:text-white transition">Mi Perfil</a>
                            @if(Auth::user()->isAdmin())
                                <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2.5 text-sm text-red-400 hover:bg-slate-900/60 hover:text-red-300 transition">Panel Admin</a>
                            @endif
                            <hr class="border-slate-800 my-1">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full text-left block px-4 py-2.5 text-sm text-red-400 hover:bg-red-500/10 transition">Cerrar Sesión</button>
                            </form>
                        </div>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="text-sm font-bold text-slate-300 hover:text-white px-3 py-1.5 transition">Iniciar Sesión</a>
                    <a href="{{ route('register') }}" class="text-sm font-bold bg-indigo-600 hover:bg-indigo-500 text-white px-4 py-2 rounded-xl shadow-lg shadow-indigo-600/15 transition duration-200">Registrarse</a>
                @endauth
            </div>
        </div>
    </header>

    <!-- Mobile Search Bar -->
    <div class="w-full bg-[#070913] border-b border-slate-900 py-3 px-4 lg:hidden">
        <div class="relative">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21-21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.602 10.602Z" />
                </svg>
            </span>
            <input type="text" id="mobile-search" placeholder="Buscar activos..." class="w-full bg-slate-950/70 border border-slate-800 rounded-xl py-2 pl-9 pr-4 text-sm text-slate-200 placeholder-slate-500 focus:outline-none focus:border-indigo-500 transition duration-200" autocomplete="off">
            <div id="mobile-search-results" class="absolute left-0 right-0 mt-2 bg-[#0d1222] border border-slate-800 rounded-xl shadow-2xl hidden max-h-60 overflow-y-auto z-50"></div>
        </div>
    </div>

    <!-- Global Notifications / Alerts -->
    @if(!Route::is('portfolio') && !Route::is('assets.show') && !Route::is('profile.edit'))
        <div class="max-w-7xl mx-auto px-4 lg:px-6 mt-4">
            @if(session('success'))
                <div class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs flex gap-3 items-start shadow-lg shadow-emerald-950/20">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 text-emerald-400 shrink-0 mt-0.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                    <div class="leading-relaxed font-medium">
                        {!! session('success') !!}
                    </div>
                </div>
            @endif
            @if(session('error'))
                <div class="p-4 rounded-2xl bg-red-500/10 border border-red-500/20 text-red-400 text-xs flex gap-3 items-start shadow-lg shadow-red-950/20">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5 text-red-405 shrink-0 mt-0.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                    </svg>
                    <div class="leading-relaxed font-medium">
                        {!! session('error') !!}
                    </div>
                </div>
            @endif
        </div>
    @endif

    <!-- Main Content -->
    <main class="flex-grow max-w-7xl w-full mx-auto px-4 lg:px-6 py-6 lg:py-8">
        @if(!$isPaperMode)
            <!-- Live Trading Active Alert -->
            <div class="mb-6 p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-450 text-xs flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 shadow-lg shadow-emerald-950/20 animate-live-glow">
                <div class="flex items-center gap-3">
                    <span class="relative flex h-3 w-3 shrink-0">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-450 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                    </span>
                    <div>
                        <strong class="text-white text-sm block">MODO REAL ACTIVO (DINERO EN VIVO)</strong>
                        <span class="text-slate-400 font-medium">Todas las operaciones ejecutadas por el bot o manualmente se realizarán con fondos reales de tu bróker.</span>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-[9px] font-black uppercase tracking-widest bg-emerald-950/40 px-2.5 py-1 rounded-md border border-emerald-500/35">OPERANDO EN DIRECTO</span>
                </div>
            </div>
        @endif
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="w-full bg-gradient-to-b from-slate-950 via-[#0a0f24] to-slate-950 border-t border-indigo-500/20 pt-16 pb-8 text-xs text-slate-500" x-data="{ showPrivacyModal: false, showTermsModal: false }">
        <div class="max-w-7xl mx-auto px-4 lg:px-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-12">
                <!-- Col 1: Brand Info -->
                <div class="space-y-4 col-span-1 md:col-span-2">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-indigo-600 flex items-center justify-center text-white shadow-md shadow-indigo-600/20">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
                                <path fill-rule="evenodd" d="M2.25 13.5a8.25 8.25 0 0 1 8.25-8.25.75.75 0 0 1 .75.75v6.75H18a.75.75 0 0 1 .75.75 8.25 8.25 0 0 1-16.5 0Z" clip-rule="evenodd" />
                                <path fill-rule="evenodd" d="M12.75 3a.75.75 0 0 1 .75-.75 8.25 8.25 0 0 1 8.25 8.25.75.75 0 0 1-.75.75h-7.5a.75.75 0 0 1-.75-.75V3Z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <span class="font-black text-lg tracking-wider bg-gradient-to-r from-white via-slate-100 to-slate-400 bg-clip-text text-transparent">GoInvesting</span>
                    </div>
                    <p class="text-slate-400 text-[13px] leading-relaxed max-w-sm">
                        Optimiza tus decisiones financieras con datos en tiempo real y automatización de operaciones inteligentes. Tu control completo sobre el mercado, en un solo lugar.
                    </p>

                </div>

                <!-- Col 2: Platform Links -->
                <div class="space-y-4">
                    <h4 class="text-xs font-bold text-white uppercase tracking-widest">Plataforma</h4>
                    <ul class="space-y-2">
                        <li>
                            <a href="{{ route('portfolio') }}" class="text-slate-400 hover:text-indigo-400 transition duration-150">Mi Portafolio</a>
                        </li>
                        <li>
                            <a href="/" class="text-slate-400 hover:text-indigo-400 transition duration-150">Mercados en Vivo</a>
                        </li>
                        @auth
                        <li>
                            <a href="{{ route('profile.edit') }}" class="text-slate-400 hover:text-indigo-400 transition duration-150">Configuración del Bot</a>
                        </li>
                        @endauth
                    </ul>
                </div>

                <!-- Col 3: Company & Contact -->
                <div class="space-y-4">
                    <h4 class="text-xs font-bold text-white uppercase tracking-widest">Enlaces</h4>
                    <ul class="space-y-2">
                        <li>
                            <a href="/#como-funcionamos" class="text-slate-400 hover:text-indigo-400 transition duration-150">Cómo Funcionamos</a>
                        </li>
                        <li>
                            <a href="/#quienes-somos" class="text-slate-400 hover:text-indigo-400 transition duration-150">Quiénes Somos</a>
                        </li>
                        <li>
                            <a href="/#contacto" class="text-slate-400 hover:text-indigo-400 transition duration-150">Contacto</a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Footer Bottom -->
            <div class="border-t border-slate-900/60 pt-8 flex flex-col md:flex-row items-center justify-between gap-6">
                <div class="flex flex-col gap-1 text-center md:text-left">
                    <p class="text-[11px] text-slate-550 leading-relaxed max-w-2xl">
                        Aviso: El trading de activos financieros conlleva riesgos. Los datos de mercados son de carácter meramente informativo y de referencia, suministrados a través de las APIs gratuitas de Yahoo Finance y Alpaca.
                    </p>
                    <p class="text-[11px] text-slate-400 mt-1">
                        &copy; {{ date('Y') }} GoInvesting. Todos los derechos reservados.
                    </p>
                </div>
                <div class="flex gap-4 shrink-0 text-[11px] text-slate-500">
                    <button @click="showPrivacyModal = true" class="hover:text-indigo-400 transition duration-150 cursor-pointer focus:outline-none">Política de Privacidad</button>
                    <span>&bull;</span>
                    <button @click="showTermsModal = true" class="hover:text-indigo-400 transition duration-150 cursor-pointer focus:outline-none">Términos de Servicio</button>
                </div>
            </div>
        </div>

        <!-- Privacy Policy Modal -->
        <div x-show="showPrivacyModal" 
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             x-cloak>
            <div class="relative w-full max-w-lg rounded-2xl glass-panel border border-slate-800/80 p-6 shadow-2xl max-h-[85vh] overflow-y-auto space-y-4" @click.away="showPrivacyModal = false">
                <div class="flex items-center justify-between border-b border-slate-800/60 pb-3">
                    <h3 class="text-sm font-bold text-white uppercase tracking-wider">Política de Privacidad</h3>
                    <button @click="showPrivacyModal = false" class="text-slate-400 hover:text-white transition">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="text-[12px] text-slate-300 space-y-3 leading-relaxed">
                    <p><strong>1. Confidencialidad de los Datos:</strong> En GoInvesting nos tomamos muy en serio la seguridad. Esta plataforma opera bajo un modelo de trading simulado con fines demostrativos y educativos.</p>
                    <p><strong>2. Almacenamiento Seguro de Credenciales:</strong> Si configuras tus claves API de Alpaca, éstas se guardan de forma encriptada en tu perfil. Únicamente se utilizan para interactuar con la plataforma de simulación (Paper Trading) de Alpaca Markets. <strong>Recomendamos encarecidamente utilizar solo credenciales Sandbox (de prueba)</strong>.</p>
                    <p><strong>3. Cookies y Sesión:</strong> Usamos cookies esenciales para mantener tu sesión activa y proteger los formularios mediante tokens CSRF frente a accesos no autorizados.</p>
                </div>
                <div class="pt-3 border-t border-slate-800/60 flex justify-end">
                    <button @click="showPrivacyModal = false" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg font-bold text-xs uppercase tracking-wider transition duration-150">Cerrar</button>
                </div>
            </div>
        </div>

        <!-- Terms of Service Modal -->
        <div x-show="showTermsModal" 
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             x-cloak>
            <div class="relative w-full max-w-lg rounded-2xl glass-panel border border-slate-800/80 p-6 shadow-2xl max-h-[85vh] overflow-y-auto space-y-4" @click.away="showTermsModal = false">
                <div class="flex items-center justify-between border-b border-slate-800/60 pb-3">
                    <h3 class="text-sm font-bold text-white uppercase tracking-wider">Términos de Servicio</h3>
                    <button @click="showTermsModal = false" class="text-slate-400 hover:text-white transition">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="text-[12px] text-slate-300 space-y-3 leading-relaxed">
                    <div class="p-3 rounded-lg bg-amber-500/10 border border-amber-500/20 text-amber-400 font-medium">
                        ⚠️ AVISO DE RIESGO: El trading financiero conlleva pérdidas potenciales de capital. Utiliza esta herramienta con prudencia.
                    </div>
                    <p><strong>1. Uso de Simulación:</strong> Las herramientas de GoInvesting ejecutan compras y ventas automatizadas basadas en límites paramétricos. Todo el entorno está optimizado para cuentas simuladas ("Paper Trading"). El usuario asume plena responsabilidad si conecta claves reales con fondos reales.</p>
                    <p><strong>2. Origen de los Datos:</strong> Las cotizaciones en vivo se obtienen de Yahoo Finance y Alpaca. Pueden contener desfases de tiempo. No garantizamos la precisión absoluta del precio en milisegundos.</p>
                    <p><strong>3. Exclusión de Asesoramiento:</strong> GoInvesting no provee recomendaciones de inversión. Eres responsable de definir tus propios límites de gasto y mitigación de riesgos.</p>
                </div>
                <div class="pt-3 border-t border-slate-800/60 flex justify-end">
                    <button @click="showTermsModal = false" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg font-bold text-xs uppercase tracking-wider transition duration-150">Aceptar</button>
                </div>
            </div>
        </div>
    </footer>

    <!-- Search Autocomplete Logic -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            setupSearch('global-search', 'search-results');
            setupSearch('mobile-search', 'mobile-search-results');
        });

        function setupSearch(inputId, resultsId) {
            const input = document.getElementById(inputId);
            const results = document.getElementById(resultsId);
            let debounceTimer;

            if (!input || !results) return;

            input.addEventListener('input', () => {
                clearTimeout(debounceTimer);
                const query = input.value.trim();

                if (query.length < 2) {
                    results.innerHTML = '';
                    results.classList.add('hidden');
                    return;
                }

                debounceTimer = setTimeout(() => {
                    fetch(`/api/search?q=${encodeURIComponent(query)}`)
                        .then(res => res.json())
                        .then(data => {
                            results.innerHTML = '';
                            if (data.length === 0) {
                                results.innerHTML = '<div class="p-3 text-sm text-slate-500 text-center">No se encontraron resultados</div>';
                                results.classList.remove('hidden');
                                return;
                            }

                            data.forEach(item => {
                                const div = document.createElement('a');
                                div.href = `/asset/${item.symbol}`;
                                div.className = 'flex items-center justify-between p-3 hover:bg-slate-800/50 transition border-b border-slate-800/40 last:border-none cursor-pointer';
                                
                                const typeBadge = `<span class="text-[10px] px-2 py-0.5 rounded-md bg-slate-900 text-slate-400 border border-slate-800 uppercase font-semibold">${item.quoteType || 'Equity'}</span>`;
                                
                                div.innerHTML = `
                                    <div class="flex flex-col">
                                        <span class="text-sm font-bold text-slate-200">${item.symbol}</span>
                                        <span class="text-xs text-slate-400">${item.shortname || item.longname || ''}</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs text-slate-500 font-semibold">${item.exchange}</span>
                                        ${typeBadge}
                                    </div>
                                `;
                                results.appendChild(div);
                            });

                            results.classList.remove('hidden');
                        })
                        .catch(err => console.error(err));
                }, 300);
            });

            // Close results on click outside
            document.addEventListener('click', (e) => {
                if (!input.contains(e.target) && !results.contains(e.target)) {
                    results.classList.add('hidden');
                }
            });
        }
    </script>

    <!-- Alpaca Support Assistant Modal -->
    @auth
    <div x-data="{ openAlpacaSupportModal: false }" 
         @open-alpaca-support.window="openAlpacaSupportModal = true"
         x-show="openAlpacaSupportModal" 
         x-cloak 
         class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm"
         x-transition>
        
        <div class="glass-panel w-full max-w-lg rounded-3xl p-6 shadow-2xl border border-slate-800/80 bg-[#0d1222]/95 space-y-6 relative overflow-hidden" @click.outside="openAlpacaSupportModal = false">
            <div class="absolute right-4 top-4">
                <button @click="openAlpacaSupportModal = false" class="text-slate-400 hover:text-white transition cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-600/10 border border-indigo-500/20 flex items-center justify-center text-indigo-400">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-3.658A8.955 8.955 0 0 1 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z" />
                    </svg>
                </div>
                <div class="text-left">
                    <h3 class="text-base font-extrabold text-white">Asistente de Soporte Alpaca</h3>
                    <p class="text-xs text-slate-400">Te ayudamos a resolver problemas con tu cuenta de inversión real</p>
                </div>
            </div>

            <!-- Status banner -->
            <div class="p-3.5 rounded-xl border border-indigo-500/10 bg-indigo-500/5 text-indigo-300 text-xs font-bold leading-normal text-left flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 shrink-0">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25.041-.02a.75.75 0 1 1 .513 1.293l-.042.015-1.478.492a1 1 0 0 0-.674.933V15m3.75 2.25h.008v.008H13v-.008Z" />
                </svg>
                <span>Canales Oficiales: Correo de Soporte y Centro de Ayuda</span>
            </div>

            <div class="space-y-4 text-xs font-medium text-slate-350 leading-relaxed text-left">
                <p>
                    Las cuentas reales de Alpaca requieren un proceso obligatorio de verificación manual para cumplir con las regulaciones de la SEC y FINRA. Si tus credenciales dan error, el motivo habitual es que tu cuenta aún está en revisión en el broker.
                </p>

                <div class="space-y-2.5 bg-slate-950/40 p-4 rounded-xl border border-slate-900">
                    <div class="font-bold text-white flex items-center gap-1.5">
                        <span class="w-1.5 h-3 bg-indigo-500 rounded"></span>
                        Opciones de Contacto Directo:
                    </div>
                    <ul class="list-disc pl-4 space-y-2 text-slate-400">
                        <li>
                            <strong class="text-slate-200">Soporte por Email (Recomendado):</strong> Puedes escribir directamente a <a href="mailto:support@alpaca.markets?subject=Estado de Cuenta Real - Alpaca&body=Hola equipo de Alpaca,%0D%0A%0D%0AMi cuenta real de Alpaca no está validada todavía y no puedo conectar mis API Keys. ¿Podrían revisar el estado de mi aprobación?%0D%0A%0D%0AEmail de registro de mi cuenta: {{ Auth::user()->email }}%0D%0ANombre: {{ Auth::user()->name }}" class="text-indigo-400 hover:text-indigo-300 font-bold underline">support@alpaca.markets</a>. Te responderán habitualmente en un plazo de 24 horas laborables.
                        </li>
                        <li>
                            <strong class="text-slate-200">Centro de Soporte Oficial:</strong> Consulta documentación, estados de cuenta y abre incidencias en la <a href="https://alpaca.markets/support" target="_blank" class="text-indigo-400 hover:text-indigo-300 font-bold underline">página de soporte de Alpaca</a>.
                        </li>
                    </ul>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row gap-3 pt-2">
                <a href="mailto:support@alpaca.markets?subject=Estado de Cuenta Real - Alpaca&body=Hola equipo de Alpaca,%0D%0A%0D%0AMi cuenta real de Alpaca no está validada todavía y no puedo conectar mis API Keys. ¿Podrían revisar el estado de mi aprobación?%0D%0A%0D%0AEmail de registro de mi cuenta: {{ Auth::user()->email }}%0D%0ANombre: {{ Auth::user()->name }}" 
                   class="flex-1 inline-flex justify-center items-center gap-1.5 bg-slate-900 border border-slate-800 text-slate-200 font-bold text-xs py-2.5 px-4 rounded-xl hover:bg-slate-800 transition cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 text-slate-400">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                    </svg>
                    Enviar Email de Consulta
                </a>
                
                <a href="https://alpaca.markets/support" target="_blank" 
                   class="flex-1 inline-flex justify-center items-center gap-1.5 bg-indigo-650 hover:bg-indigo-550 text-white font-bold text-xs py-2.5 px-4 rounded-xl transition shadow-md shadow-indigo-650/10 cursor-pointer">
                    Centro de Soporte Alpaca
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3.5 h-3.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                    </svg>
                </a>
            </div>
        </div>
    </div>
    @endauth

    <!-- IntersectionObserver for Header Links ScrollSpy -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const sections = document.querySelectorAll('#como-funcionamos, #quienes-somos, #contacto');
            if (sections.length === 0) return;

            const observerOptions = {
                root: null,
                rootMargin: '-20% 0px -60% 0px',
                threshold: 0
            };

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const id = entry.target.getAttribute('id');
                        window.dispatchEvent(new CustomEvent('section-change', { detail: '#' + id }));
                    }
                });
            }, observerOptions);

            sections.forEach(section => observer.observe(section));

            // Clear highlight if scrolled back to top
            window.addEventListener('scroll', () => {
                if (window.scrollY < 200) {
                    window.dispatchEvent(new CustomEvent('section-change', { detail: '' }));
                }
            });
        });
    </script>
</body>
</html>
