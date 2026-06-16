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
        body {
            font-family: 'Plus Jakarta Sans', 'Outfit', sans-serif;
            background-color: #070913;
            color: #f8fafc;
            overflow-x: hidden;
        }
        
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
    </style>
</head>
<body class="min-h-screen flex flex-col antialiased selection:bg-indigo-500 selection:text-white">

    <!-- Top Live Ticker -->
    @if(!empty($tickerQuotes))
    <div class="w-full bg-[#030712] border-b border-slate-900 py-2.5 overflow-visible text-xs relative z-50">
        <div class="max-w-7xl mx-auto px-4 lg:px-6 flex items-center gap-4">
            
            <!-- Info Badge for Beginners -->
            <div class="flex items-center gap-1.5 px-2.5 py-1 bg-indigo-950/30 border border-indigo-500/20 rounded-lg text-[10px] text-indigo-300 font-bold cursor-help group relative select-none shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3.5 h-3.5 text-indigo-400">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 1 1 .513 1.293l-.042.015-1.478.492a1 1 0 0 0-.674.933V15m3.75 2.25h.008v.008H13v-.008Z" />
                </svg>
                <span>Mercados en Vivo</span>
                
                <!-- Custom elegant tooltip -->
                <div class="absolute left-0 top-full mt-2 w-72 p-4 bg-[#0d1222] border border-slate-800 text-slate-350 rounded-xl shadow-2xl z-50 hidden group-hover:block whitespace-normal text-xs font-medium leading-relaxed">
                    <strong class="text-white block mb-1.5">Índices y Activos de Referencia</strong>
                    Estos valores muestran la tendencia en tiempo real de los principales indicadores económicos mundiales (bolsas, materias primas y criptomonedas). 
                    <hr class="border-slate-800 my-2">
                    <span class="text-[10px] text-slate-500 block">Fuente de datos en tiempo real: Yahoo Finance.</span>
                </div>
            </div>

            <!-- Ticker Items Carousel -->
            <div class="flex-grow flex items-center gap-6 overflow-x-auto no-scrollbar scroll-smooth whitespace-nowrap overflow-visible">
                @foreach($tickerQuotes as $symbol => $quote)
                    @php
                        $isPositive = ($quote['changePercent'] ?? 0) >= 0;
                        $colorClass = $isPositive ? 'text-green-400' : 'text-red-400';
                        $symbolClean = str_replace(['=X', '^'], '', $symbol);
                        $meta = $tickerMetadata[$symbol] ?? ['name' => $symbolClean, 'desc' => ''];
                    @endphp
                    <div class="relative group inline-flex items-center">
                        <a href="{{ route('assets.show', $symbol) }}" data-symbol-ticker="{{ $symbol }}" class="inline-flex items-center gap-2 hover:opacity-85 transition duration-150 border-r border-slate-800/80 pr-6 last:border-none">
                            <span class="font-bold text-slate-200">{{ $meta['name'] }}</span>
                            <span class="text-[9px] text-slate-500 font-bold uppercase tracking-wider">[{{ $symbolClean }}]</span>
                            <span class="font-medium text-slate-100" data-field="price">${{ number_format($quote['price'] ?? 0, 2) }}</span>
                            <span class="flex items-center gap-0.5 font-bold {{ $colorClass }}" data-field="change-badge">
                                <span data-field="direction">{{ $isPositive ? '▲' : '▼' }}</span>
                                <span data-field="changePercent">{{ number_format(abs($quote['changePercent'] ?? 0), 2) }}%</span>
                            </span>
                        </a>

                        <!-- Custom elegant item tooltip -->
                        <div class="absolute left-0 top-full mt-2 w-64 p-3 bg-[#0d1222]/95 backdrop-blur-md border border-slate-800/80 text-slate-350 rounded-xl shadow-2xl z-50 hidden group-hover:block whitespace-normal text-[11px] font-normal leading-relaxed">
                            <strong class="text-white block mb-0.5">{{ $meta['name'] }} ({{ $symbolClean }})</strong>
                            {{ $meta['desc'] }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- Main Navigation Header -->
    <header class="sticky top-0 z-40 w-full bg-[#070913]/85 backdrop-blur-md border-b border-slate-900">
        <div class="max-w-7xl mx-auto px-4 lg:px-6 h-16 flex items-center justify-between gap-4">
            
            <!-- Logo -->
            <a href="{{ route('home') }}" class="flex items-center gap-2.5 select-none shrink-0 group">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-indigo-600 to-violet-500 flex items-center justify-center shadow-lg shadow-indigo-550/20 group-hover:scale-105 transition-all duration-300">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5 text-white">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.307a11.95 11.95 0 0 0 5.814-5.519l2.74-1.22m0 0-5.94-2.28m5.94 2.28-2.28 5.941" />
                    </svg>
                </div>
                <span class="font-extrabold text-xl tracking-tight bg-gradient-to-r from-white via-slate-100 to-indigo-300 bg-clip-text text-transparent">Go<span class="text-indigo-400 font-bold">Investing</span></span>
            </a>

            <!-- Autocomplete Search Bar -->
            <div class="relative flex-1 max-w-md mx-4 hidden md:block">
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21-21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.602 10.602Z" />
                        </svg>
                    </span>
                    <input type="text" id="global-search" placeholder="Buscar acciones, índices, criptomonedas..." class="w-full bg-slate-950/70 border border-slate-800 rounded-xl py-2 pl-10 pr-4 text-sm text-slate-200 placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500/55 transition duration-200" autocomplete="off">
                </div>
                <!-- Search Floating Dropdown -->
                <div id="search-results" class="absolute left-0 right-0 mt-2 bg-[#0d1222] border border-slate-800 rounded-xl shadow-2xl hidden max-h-80 overflow-y-auto z-50"></div>
            </div>

            <!-- Auth Actions -->
            <div class="flex items-center gap-3 shrink-0">
                @auth
                    <a href="{{ route('portfolio') }}" class="text-xs font-bold text-slate-300 hover:text-white px-3 py-1.5 rounded-xl border border-slate-800 hover:bg-slate-900/40 transition duration-150 flex items-center gap-1.5">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3.5 h-3.5 text-indigo-400">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5h16.5M5.25 7.5h13.5m-12 3h10.5m-9 3h7.5m-6 3h4.5m-3.75 3h3" />
                        </svg>
                        Mi Portafolio
                    </a>
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
    <div class="w-full bg-[#070913] border-b border-slate-900 py-3 px-4 md:hidden">
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

    <!-- Main Content -->
    <main class="flex-grow max-w-7xl w-full mx-auto px-4 lg:px-6 py-6 lg:py-8">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="w-full bg-[#030712] border-t border-slate-900 py-8 text-center text-xs text-slate-500">
        <div class="max-w-7xl mx-auto px-4 lg:px-6 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-2">
                <span class="font-extrabold text-sm text-slate-400">GoInvesting</span>
                <span>&copy; {{ date('Y') }} Todos los derechos reservados.</span>
            </div>
            <div class="flex items-center gap-4 text-slate-400">
                <span class="text-slate-600">Datos proporcionados por la API gratuita de Yahoo Finance.</span>
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
    @php
        $nowEST = \Carbon\Carbon::now('America/New_York');
        $isAlpacaChatOpen = $nowEST->isWeekday() && $nowEST->hour >= 9 && $nowEST->hour < 17;
        $alpacaTimeStr = $nowEST->format('H:i') . ' EST';
    @endphp
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
            <div class="p-3.5 rounded-xl border flex items-center justify-between text-xs font-bold leading-normal text-left {{ $isAlpacaChatOpen ? 'bg-emerald-500/10 border-emerald-500/20 text-emerald-400' : 'bg-slate-900/60 border-slate-800 text-slate-400' }}">
                <div class="flex items-center gap-2">
                    <span class="relative flex h-2 w-2">
                        <span class="absolute inline-flex h-full w-full rounded-full opacity-75 {{ $isAlpacaChatOpen ? 'animate-ping bg-emerald-400' : 'bg-slate-500' }}"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 {{ $isAlpacaChatOpen ? 'bg-emerald-500' : 'bg-slate-500' }}"></span>
                    </span>
                    <span>Chat de Soporte Alpaca: {{ $isAlpacaChatOpen ? 'ABIERTO' : 'CERRADO ACTUALMENTE' }}</span>
                </div>
                <span class="text-[10px] opacity-80">Hora en NY: {{ $alpacaTimeStr }}</span>
            </div>

            <div class="space-y-4 text-xs font-medium text-slate-350 leading-relaxed text-left">
                <p>
                    Las cuentas reales de Alpaca requieren un proceso obligatorio de verificación manual para cumplir con las regulaciones de la SEC y FINRA. Si tus credenciales dan error, el motivo habitual es que tu cuenta aún está en proceso de revisión.
                </p>

                <div class="space-y-2.5 bg-slate-950/40 p-4 rounded-xl border border-slate-900">
                    <div class="font-bold text-white flex items-center gap-1.5">
                        <span class="w-1.5 h-3 bg-indigo-500 rounded"></span>
                        Opciones de Contacto Directo:
                    </div>
                    <ul class="list-disc pl-4 space-y-2 text-slate-400">
                        <li>
                            <strong class="text-slate-200">Chat en Vivo (Recomendado):</strong> Disponible de Lunes a Viernes de 9:00 a 17:00 EST. Requiere que inicies sesión en tu <a href="https://app.alpaca.markets" target="_blank" class="text-indigo-400 hover:text-indigo-300 font-bold underline">consola de Alpaca</a> y abras el globo de chat en la esquina inferior derecha.
                        </li>
                        <li>
                            <strong class="text-slate-200">Soporte por Email:</strong> Puedes escribir directamente a <a href="mailto:support@alpaca.markets?subject=Estado de Cuenta Real - Alpaca&body=Hola equipo de Alpaca,%0D%0A%0D%0AMi cuenta real de Alpaca no está validada todavía y no puedo conectar mis API Keys. ¿Podrían revisar el estado de mi aprobación?%0D%0A%0D%0AEmail de registro de mi cuenta: {{ Auth::user()->email }}%0D%0ANombre: {{ Auth::user()->name }}" class="text-indigo-400 hover:text-indigo-300 font-bold underline">support@alpaca.markets</a>. Te responderán habitualmente en un plazo de 24 horas.
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
                
                <a href="https://app.alpaca.markets" target="_blank" 
                   class="flex-1 inline-flex justify-center items-center gap-1.5 bg-indigo-650 hover:bg-indigo-550 text-white font-bold text-xs py-2.5 px-4 rounded-xl transition shadow-md shadow-indigo-650/10 cursor-pointer">
                    Ir al Dashboard de Alpaca
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3.5 h-3.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                    </svg>
                </a>
            </div>
        </div>
    </div>
    @endauth
</body>
</html>
