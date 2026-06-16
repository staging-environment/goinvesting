@extends('layouts.layout')

@section('title', 'GoInvesting | Tu Dashboard de Mercados Financieros')

@section('content')
@php
    $friendlyNames = [
        // Indices
        '^GSPC' => 'S&P 500',
        '^DJI' => 'Dow Jones',
        '^IXIC' => 'NASDAQ',
        '^FTSE' => 'FTSE 100',
        '^GDAXI' => 'DAX 40',
        '^N225' => 'Nikkei 225',
        '^IBEX' => 'IBEX 35',
        '^FCHI' => 'CAC 40',
        '^STOXX50E' => 'Euro Stoxx 50',
        '^HSI' => 'Hang Seng',

        // Stocks
        'AAPL' => 'Apple',
        'MSFT' => 'Microsoft',
        'GOOGL' => 'Google',
        'AMZN' => 'Amazon',
        'TSLA' => 'Tesla',
        'NVDA' => 'Nvidia',
        'META' => 'Meta (Facebook)',
        'NFLX' => 'Netflix',
        'AMD' => 'AMD',
        'JPM' => 'JPMorgan Chase',

        // Forex
        'EURUSD=X' => 'EUR / USD',
        'GBPUSD=X' => 'GBP / USD',
        'USDJPY=X' => 'USD / JPY',
        'AUDUSD=X' => 'AUD / USD',
        'USDCAD=X' => 'USD / CAD',
        'EURGBP=X' => 'EUR / GBP',
        'USDCHF=X' => 'USD / CHF',
        'EURJPY=X' => 'EUR / JPY',
        'GBPJPY=X' => 'GBP / JPY',
        'NZDUSD=X' => 'NZD / USD',

        // Crypto
        'BTC-USD' => 'Bitcoin',
        'ETH-USD' => 'Ethereum',
        'SOL-USD' => 'Solana',
        'BNB-USD' => 'Binance Coin',
        'ADA-USD' => 'Cardano',
        'XRP-USD' => 'Ripple',
        'DOT-USD' => 'Polkadot',
        'DOGE-USD' => 'Dogecoin',
        'AVAX-USD' => 'Avalanche',
        'LINK-USD' => 'Chainlink',

        // Commodities
        'GC=F' => 'Oro',
        'CL=F' => 'Petróleo Crudo',
        'SI=F' => 'Plata',
        'NG=F' => 'Gas Natural',
        'BZ=F' => 'Petróleo Brent',
        'HG=F' => 'Cobre',
        'PL=F' => 'Platino',
        'PA=F' => 'Paladio',
        'ZC=F' => 'Maíz',
        'ZW=F' => 'Trigo'
    ];
@endphp
<div class="space-y-8" x-data="{ activeTab: 'indices' }">
    
    <!-- Hero / Welcome Banner -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-slate-950 via-slate-900 to-indigo-950 border border-slate-900 p-8 lg:p-12 shadow-2xl">
        <div class="absolute top-0 right-0 w-80 h-80 bg-indigo-500/10 rounded-full blur-3xl -mr-20 -mt-20"></div>
        <div class="relative z-10 max-w-2xl space-y-4">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
                <span class="w-1.5 h-1.5 rounded-full bg-indigo-400 animate-pulse"></span>
                Datos en tiempo real / diferidos
            </span>
            <h1 class="text-3xl lg:text-4xl font-extrabold tracking-tight text-white leading-tight">
                Plataforma de Seguimiento de Mercados Globales
            </h1>
            <p class="text-sm text-slate-400 leading-relaxed max-w-lg">
                Analiza las principales bolsas del mundo, materias primas, pares de divisas y criptomonedas basándote en datos actualizados y gráficos de alta precisión.
            </p>
        </div>
    </div>
 
    <!-- Leyenda de Operabilidad -->
    <div class="p-4.5 rounded-2xl bg-slate-950/40 border border-slate-900/60 flex flex-col lg:flex-row lg:items-center justify-between gap-4 text-xs">
        <div class="flex items-center gap-2 text-slate-300">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 text-indigo-400 shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 1 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.852l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
            </svg>
            <span class="font-bold text-slate-200">Disponibilidad Operativa en Bróker (Alpaca):</span>
        </div>
        <div class="flex flex-col sm:flex-row sm:items-center gap-4 lg:gap-8">
            <div class="flex items-start gap-2 max-w-sm">
                <span class="text-[9px] px-2 py-0.5 rounded-md font-bold uppercase tracking-wider bg-emerald-500/10 text-emerald-400 border border-emerald-500/15 mt-0.5 shrink-0">Operable</span>
                <span class="text-slate-400 text-[11px] leading-relaxed">Activo disponible para comprar y vender directamente con tus credenciales de Alpaca.</span>
            </div>
            <div class="flex items-start gap-2 max-w-sm">
                <span class="text-[9px] px-2 py-0.5 rounded-md font-bold uppercase tracking-wider bg-slate-500/10 text-slate-400 border border-slate-500/15 mt-0.5 shrink-0">Info</span>
                <span class="text-slate-400 text-[11px] leading-relaxed">Activo únicamente referencial de mercado. No se puede negociar directamente en el bróker.</span>
            </div>
        </div>
    </div>

    <!-- Main Dashboard Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Left: Markets Tabs & Tables (Col Span 2) -->
        <div class="lg:col-span-2 space-y-4">
            
            <!-- Tab Controls -->
            <div class="flex items-center gap-2 overflow-x-auto pb-2 border-b border-slate-900 no-scrollbar">
                @foreach([
                    'indices' => 'Índices',
                    'stocks' => 'Acciones',
                    'forex' => 'Forex',
                    'crypto' => 'Cripto',
                    'commodities' => 'Materias Primas'
                ] as $key => $label)
                    <button 
                        @click="activeTab = '{{ $key }}'" 
                        :class="activeTab === '{{ $key }}' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/15' : 'text-slate-400 hover:text-slate-200 bg-slate-950/40 border border-slate-900/80'" 
                        class="px-4 py-2 text-sm font-bold rounded-xl transition duration-200 cursor-pointer shrink-0"
                    >
                        {{ $label }}
                    </button>
                @endforeach
            </div>

            <!-- Market Tables Container -->
            <div class="glass-panel rounded-2xl overflow-hidden shadow-xl">
                @foreach(['indices', 'stocks', 'forex', 'crypto', 'commodities'] as $tabKey)
                    <div x-show="activeTab === '{{ $tabKey }}'" x-transition:enter="transition ease-out duration-200" class="overflow-x-auto">
                        <table class="w-full text-left border-collapse min-w-[600px]">
                            <thead>
                                <tr class="border-b border-slate-900 text-xs font-bold uppercase tracking-wider text-slate-500 bg-[#070913]/30">
                                    <th class="py-4 px-5">Activo</th>
                                    <th class="py-4 px-5 text-right">Precio</th>
                                    <th class="py-4 px-5 text-right">Cambio</th>
                                    <th class="py-4 px-5 text-right">Cambio %</th>
                                    <th class="py-4 px-5 text-center w-28">Tendencia (24h)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-900/50">
                                @if(empty($$tabKey))
                                    <tr>
                                        <td colspan="5" class="py-8 text-center text-sm text-slate-500">Cargando datos del mercado...</td>
                                    </tr>
                                @else
                                    @foreach($$tabKey as $quote)
                                        @php
                                            $isPositive = ($quote['changePercent'] ?? 0) >= 0;
                                            $colorClass = $isPositive ? 'text-green-400' : 'text-red-400';
                                            $bgColorClass = $isPositive ? 'bg-green-500/10' : 'bg-red-500/10';
                                            $sparkColor = $isPositive ? '#4ade80' : '#f87171';
                                            $symbolClean = str_replace(['=X', '^'], '', $quote['symbol']);
                                        @endphp
                                        <tr data-symbol-row="{{ $quote['symbol'] }}" class="hover:bg-slate-950/40 transition duration-150 group cursor-pointer" onclick="window.location.href='{{ route('assets.show', $quote['symbol']) }}'">
                                            <td class="py-4.5 px-5">
                                                <div class="flex flex-col">
                                                    @php
                                                        $friendlyName = $friendlyNames[$quote['symbol']] ?? $quote['shortName'] ?? $symbolClean;
                                                        $isQuoteTradeable = !str_starts_with($quote['symbol'], '^') && !str_contains($quote['symbol'], '=X') && !str_contains($quote['symbol'], '=F');
                                                    @endphp
                                                    <div class="flex items-center gap-2">
                                                        <span class="font-extrabold text-sm text-white group-hover:text-indigo-400 transition">{{ $friendlyName }}</span>
                                                        @if($isQuoteTradeable)
                                                            <span class="text-[8px] px-1.5 py-0.5 rounded-md font-bold uppercase tracking-wider bg-emerald-500/10 text-emerald-400 border border-emerald-500/10">Operable</span>
                                                        @else
                                                            <span class="text-[8px] px-1.5 py-0.5 rounded-md font-bold uppercase tracking-wider bg-slate-500/10 text-slate-400 border border-slate-500/10">Info</span>
                                                        @endif
                                                    </div>
                                                    <span class="text-[10px] text-slate-500 font-medium">{{ $symbolClean }}</span>
                                                </div>
                                            </td>
                                            <td class="py-4.5 px-5 text-right font-bold text-slate-100 text-sm" data-field="price">
                                                ${{ number_format($quote['price'] ?? 0, 2) }}
                                            </td>
                                            <td class="py-4.5 px-5 text-right font-semibold {{ $colorClass }} text-sm" data-field="change">
                                                {{ $isPositive ? '+' : '' }}{{ number_format($quote['change'] ?? 0, 2) }}
                                            </td>
                                            <td class="py-4.5 px-5 text-right">
                                                <span data-field="changePercent-badge" class="inline-block px-2.5 py-1 rounded-lg text-xs font-bold {{ $colorClass }} {{ $bgColorClass }}">
                                                    <span data-field="changePercent">{{ $isPositive ? '+' : '' }}{{ number_format($quote['changePercent'] ?? 0, 2) }}%</span>
                                                </span>
                                            </td>
                                            <td class="py-3 px-5 text-center">
                                                @if(!empty($quote['sparkline']))
                                                    @php
                                                        $min = min($quote['sparkline']);
                                                        $max = max($quote['sparkline']);
                                                        $range = ($max - $min) > 0 ? ($max - $min) : 1;
                                                        $points = [];
                                                        $count = count($quote['sparkline']);
                                                        foreach ($quote['sparkline'] as $idx => $val) {
                                                            $x = ($idx / ($count - 1)) * 100;
                                                            $y = 100 - (($val - $min) / $range) * 85 - 7;
                                                            $points[] = "$x,$y";
                                                        }
                                                        $pointsStr = implode(' ', $points);
                                                    @endphp
                                                    <svg class="w-24 h-8 mx-auto" viewBox="0 0 100 100" preserveAspectRatio="none">
                                                        <polyline fill="none" stroke="{{ $sparkColor }}" stroke-width="3" points="{{ $pointsStr }}" />
                                                    </svg>
                                                @else
                                                    <span class="text-xs text-slate-600">—</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Right Column: Watchlist & News -->
        <div class="space-y-8">
            
            @if(Auth::check() && !empty($alpacaAccount))
            <!-- Portfolio Summary Card -->
            <div class="glass-panel rounded-2xl p-6 shadow-xl space-y-4 bg-gradient-to-tr from-slate-900 to-indigo-950/30">
                <h2 class="text-base font-extrabold text-white flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-indigo-400">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5h16.5M5.25 7.5h13.5m-12 3h10.5m-9 3h7.5m-6 3h4.5m-3.75 3h3" />
                    </svg>
                    Resumen de Cartera
                </h2>
                
                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col">
                        <span class="text-[10px] text-slate-500 font-bold uppercase">Patrimonio Neto</span>
                        <span class="text-lg font-extrabold text-white">${{ number_format($alpacaAccount['portfolio_value'] ?? 0, 2) }}</span>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-[10px] text-slate-500 font-bold uppercase">Poder de Compra</span>
                        <span class="text-lg font-extrabold text-indigo-400">${{ number_format($alpacaAccount['buying_power'] ?? 0, 2) }}</span>
                    </div>
                </div>

                <div class="border-t border-slate-800/80 pt-3 flex justify-between items-center text-xs">
                    <span class="text-slate-500 font-semibold">{{ str_contains($alpacaAccount['currency'] ?? 'USD', 'USD') && config('services.alpaca.is_paper') ? 'Cuenta Simulación (Paper)' : 'Cuenta Real' }}</span>
                    <a href="{{ route('portfolio') }}" class="font-bold text-indigo-400 hover:text-indigo-300 transition flex items-center gap-0.5">
                        Ver todo
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3 h-3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                        </svg>
                    </a>
                </div>
            </div>
            @endif

            @if(Auth::check() && empty($alpacaAccount))
            <!-- Alpaca Setup Onboarding Card -->
            <div class="glass-panel rounded-2xl p-6 shadow-xl space-y-4 border-amber-500/20 bg-gradient-to-tr from-slate-900 to-amber-950/20">
                <h2 class="text-base font-extrabold text-white flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-amber-400">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.008v.008H12v-.008Z" />
                    </svg>
                    Integración de Alpaca Pendiente
                </h2>
                <p class="text-xs text-slate-400 leading-relaxed font-medium">
                    Para que la plataforma pueda realizar compras y ventas (automáticas por el bot o manuales por ti), **debes conectar tu cuenta con Alpaca Broker**.
                </p>
                <div class="text-[11px] text-slate-400 space-y-1.5 bg-slate-950/40 p-3 rounded-xl border border-slate-800/50 leading-normal font-medium">
                    <div>1. Regístrate gratis en <a href="https://alpaca.markets" target="_blank" class="text-indigo-400 hover:underline font-bold">Alpaca.markets</a> (cuenta Paper de simulación).</div>
                    <div>2. Obtén tus **API Keys** de simulación (Key ID y Secret Key).</div>
                    <div>3. Introduce las llaves en tu perfil de GoInvesting.</div>
                </div>
                <div class="pt-1">
                    <a href="{{ route('profile.edit') }}" class="w-full inline-flex justify-center items-center gap-1 bg-amber-500/10 hover:bg-amber-500 border border-amber-500/30 text-amber-450 hover:text-white font-bold text-xs py-2.5 px-4 rounded-xl transition duration-150 shadow-sm shadow-amber-500/5 hover:shadow-amber-500/20">
                        Configurar Integración ahora
                    </a>
                </div>
            </div>
            @endif

            <!-- Watchlist Panel -->
            <div class="glass-panel rounded-2xl p-6 shadow-xl space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-base font-extrabold text-white flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-indigo-400">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.593 3.322c1.1.128 1.907 1.077 1.907 2.185V21L12 17.25 4.5 21V5.507c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0 1 11.186 0Z" />
                        </svg>
                        Mi Lista de Seguimiento
                    </h2>
                </div>

                @auth
                    @if(empty($watchlist))
                        <div class="py-8 text-center text-slate-500 text-xs">
                            <p>Tu lista de seguimiento está vacía.</p>
                            <p class="mt-1">Busca cualquier activo y haz clic en "Añadir a Favoritos".</p>
                        </div>
                    @else
                        <div class="divide-y divide-slate-800/60 max-h-80 overflow-y-auto pr-1">
                            @foreach($watchlist as $quote)
                                @php
                                    $isPositive = ($quote['changePercent'] ?? 0) >= 0;
                                    $colorClass = $isPositive ? 'text-green-400' : 'text-red-400';
                                    $symbolClean = str_replace(['=X', '^'], '', $quote['symbol']);
                                @endphp
                                <div data-symbol-watchlist="{{ $quote['symbol'] }}" class="flex items-center justify-between py-3 hover:bg-slate-900/30 px-2 rounded-xl transition duration-150 group cursor-pointer" onclick="window.location.href='{{ route('assets.show', $quote['symbol']) }}'">
                                    <div class="flex flex-col">
                                        @php
                                            $friendlyName = $friendlyNames[$quote['symbol']] ?? $quote['shortName'] ?? $symbolClean;
                                            $isQuoteTradeable = !str_starts_with($quote['symbol'], '^') && !str_contains($quote['symbol'], '=X') && !str_contains($quote['symbol'], '=F');
                                        @endphp
                                        <div class="flex items-center gap-2">
                                            <span class="font-extrabold text-sm text-slate-200 group-hover:text-indigo-400 transition">{{ $friendlyName }}</span>
                                            @if($isQuoteTradeable)
                                                <span class="text-[8px] px-1.5 py-0.5 rounded-md font-bold uppercase tracking-wider bg-emerald-500/10 text-emerald-400 border border-emerald-500/10">Operable</span>
                                            @else
                                                <span class="text-[8px] px-1.5 py-0.5 rounded-md font-bold uppercase tracking-wider bg-slate-500/10 text-slate-400 border border-slate-500/10">Info</span>
                                            @endif
                                        </div>
                                        <span class="text-[10px] text-slate-500 font-medium">{{ $symbolClean }}</span>
                                    </div>
                                    <div class="text-right flex flex-col">
                                        <span class="text-sm font-bold text-slate-100" data-field="price">${{ number_format($quote['price'] ?? 0, 2) }}</span>
                                        <span class="text-xs font-semibold {{ $colorClass }}" data-field="changePercent-badge">
                                            <span data-field="direction">{{ $isPositive ? '▲' : '▼' }}</span>
                                            <span data-field="changePercent">{{ number_format($quote['changePercent'] ?? 0, 2) }}%</span>
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                @else
                    <div class="border border-dashed border-slate-800 rounded-xl p-6 text-center space-y-3.5 bg-slate-950/20">
                        <p class="text-xs text-slate-400 leading-relaxed">
                            Crea una cuenta o inicia sesión para personalizar tu lista de seguimiento y acceder a un portafolio en tiempo real.
                        </p>
                        <div class="flex justify-center gap-3">
                            <a href="{{ route('login') }}" class="text-xs font-bold bg-slate-900 border border-slate-800 text-slate-200 px-3.5 py-2 rounded-xl hover:bg-slate-800/50 transition">Iniciar Sesión</a>
                            <a href="{{ route('register') }}" class="text-xs font-bold bg-indigo-600 hover:bg-indigo-500 text-white px-3.5 py-2 rounded-xl transition">Registrarse</a>
                        </div>
                    </div>
                @endauth
            </div>

            <!-- Market News Panel -->
            <div class="glass-panel rounded-2xl p-6 shadow-xl space-y-4">
                <h2 class="text-base font-extrabold text-white flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-indigo-400">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 7.5h1.5m-1.5 3h1.5m-7.5 3h7.5m-7.5 3h7.5m3-9h3.375c.621 0 1.125.504 1.125 1.125V18a2.25 2.25 0 0 1-2.25 2.25M16.5 7.5V18a2.25 2.25 0 0 0 2.25 2.25M16.5 7.5V4.875c0-.621-.504-1.125-1.125-1.125H4.125C3.504 3.75 3 4.254 3 4.875V18a2.25 2.25 0 0 0 2.25 2.25h13.5M6 7.5h3v3H6v-3Z" />
                    </svg>
                    Noticias y Análisis del Mercado
                </h2>

                <div class="space-y-4.5">
                    @php
                        $dummyNews = [
                            [
                                'category' => 'TECNOLOGÍA',
                                'title' => 'El sector tecnológico impulsa las ganancias globales en la bolsa',
                                'desc' => 'Las acciones de semiconductores registran subidas sustanciales ante la reactivación de pedidos institucionales a gran escala.',
                                'time' => 'Hace 15m'
                            ],
                            [
                                'category' => 'MERCADOS',
                                'title' => 'La FED mantiene cautela sobre las tasas antes del reporte del IPC',
                                'desc' => 'Inversores esperan las declaraciones de Jerome Powell para vislumbrar el futuro de la política monetaria estadounidense.',
                                'time' => 'Hace 1h'
                            ],
                            [
                                'category' => 'DIVISAS',
                                'title' => 'El euro busca estabilidad cerca de $1.09 tras datos de inflación alemana',
                                'desc' => 'El par EUR/USD mantiene una banda de negociación estrecha debido a los contrastes macroeconómicos de la Eurozona.',
                                'time' => 'Hace 3h'
                            ]
                        ];
                    @endphp

                    @foreach($dummyNews as $news)
                        <div class="group cursor-pointer border-b border-slate-900/60 pb-4 last:border-none last:pb-0">
                            <span class="text-[9px] font-extrabold text-indigo-400 bg-indigo-500/10 px-2 py-0.5 rounded-md border border-indigo-500/10">{{ $news['category'] }}</span>
                            <h3 class="text-sm font-bold text-slate-100 group-hover:text-indigo-400 transition duration-150 mt-2 leading-snug">{{ $news['title'] }}</h3>
                            <p class="text-xs text-slate-400 line-clamp-2 mt-1 leading-relaxed">{{ $news['desc'] }}</p>
                            <span class="text-[10px] text-slate-600 block mt-2 font-semibold">{{ $news['time'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Run refresh every 10 seconds
        setInterval(refreshMarketData, 10000);
    });

    function refreshMarketData() {
        fetch('/api/quotes')
            .then(res => res.json())
            .then(data => {
                // Flatten all quotes into a single map
                const allQuotes = {};
                
                Object.keys(data).forEach(category => {
                    if (Array.isArray(data[category])) {
                        data[category].forEach(quote => {
                            allQuotes[quote.symbol] = quote;
                        });
                    }
                });

                // Update DOM elements
                updateTickers(allQuotes);
                updateTableRows(allQuotes);
                updateWatchlists(allQuotes);
            })
            .catch(err => console.error("Error refreshing market data:", err));
    }

    function updateTickers(quotes) {
        document.querySelectorAll('[data-symbol-ticker]').forEach(ticker => {
            const symbol = ticker.getAttribute('data-symbol-ticker');
            const quote = quotes[symbol];
            if (!quote) return;

            const priceEl = ticker.querySelector('[data-field="price"]');
            const badgeEl = ticker.querySelector('[data-field="change-badge"]');
            const dirEl = ticker.querySelector('[data-field="direction"]');
            const pctEl = ticker.querySelector('[data-field="changePercent"]');

            if (priceEl && quote.price !== null) {
                const oldPrice = parseFloat(priceEl.textContent.replace(/[^\d.-]/g, ''));
                const newPrice = parseFloat(quote.price);
                priceEl.textContent = `$${newPrice.toFixed(2)}`;
                
                // Price flash animation
                if (newPrice > oldPrice) {
                    flashElement(priceEl, 'text-green-400');
                } else if (newPrice < oldPrice) {
                    flashElement(priceEl, 'text-red-400');
                }
            }

            if (badgeEl && quote.changePercent !== null) {
                const isPositive = quote.changePercent >= 0;
                
                // Update direction symbol
                if (dirEl) dirEl.textContent = isPositive ? '▲' : '▼';
                
                // Update percentage change
                if (pctEl) pctEl.textContent = `${Math.abs(quote.changePercent).toFixed(2)}%`;

                // Update text color classes
                badgeEl.className = `flex items-center gap-0.5 font-semibold ${isPositive ? 'text-green-400' : 'text-red-400'}`;
            }
        });
    }

    function updateTableRows(quotes) {
        document.querySelectorAll('[data-symbol-row]').forEach(row => {
            const symbol = row.getAttribute('data-symbol-row');
            const quote = quotes[symbol];
            if (!quote) return;

            const priceEl = row.querySelector('[data-field="price"]');
            const changeEl = row.querySelector('[data-field="change"]');
            const badgeEl = row.querySelector('[data-field="changePercent-badge"]');
            const pctEl = row.querySelector('[data-field="changePercent"]');

            if (priceEl && quote.price !== null) {
                const oldPrice = parseFloat(priceEl.textContent.replace(/[^\d.-]/g, ''));
                const newPrice = parseFloat(quote.price);
                priceEl.textContent = `$${newPrice.toFixed(2)}`;

                // Price flash animation on whole row and cell
                if (newPrice > oldPrice) {
                    flashElement(priceEl, 'text-green-400');
                    flashRow(row, 'bg-green-500/10');
                } else if (newPrice < oldPrice) {
                    flashElement(priceEl, 'text-red-400');
                    flashRow(row, 'bg-red-500/10');
                }
            }

            const isPositive = (quote.changePercent ?? 0) >= 0;
            const sign = isPositive ? '+' : '';

            if (changeEl && quote.change !== null) {
                changeEl.textContent = `${sign}${quote.change.toFixed(2)}`;
                changeEl.className = `py-4.5 px-5 text-right font-semibold text-sm ${isPositive ? 'text-green-400' : 'text-red-400'}`;
            }

            if (badgeEl && pctEl && quote.changePercent !== null) {
                pctEl.textContent = `${sign}${quote.changePercent.toFixed(2)}%`;
                badgeEl.className = `inline-block px-2.5 py-1 rounded-lg text-xs font-bold ${isPositive ? 'text-green-400 bg-green-500/10' : 'text-red-400 bg-red-500/10'}`;
            }
        });
    }

    function updateWatchlists(quotes) {
        document.querySelectorAll('[data-symbol-watchlist]').forEach(item => {
            const symbol = item.getAttribute('data-symbol-watchlist');
            const quote = quotes[symbol];
            if (!quote) return;

            const priceEl = item.querySelector('[data-field="price"]');
            const badgeEl = item.querySelector('[data-field="changePercent-badge"]');
            const dirEl = item.querySelector('[data-field="direction"]');
            const pctEl = item.querySelector('[data-field="changePercent"]');

            if (priceEl && quote.price !== null) {
                const oldPrice = parseFloat(priceEl.textContent.replace(/[^\d.-]/g, ''));
                const newPrice = parseFloat(quote.price);
                priceEl.textContent = `$${newPrice.toFixed(2)}`;

                if (newPrice > oldPrice) {
                    flashElement(priceEl, 'text-green-400');
                    flashRow(item, 'bg-green-500/10');
                } else if (newPrice < oldPrice) {
                    flashElement(priceEl, 'text-red-400');
                    flashRow(item, 'bg-red-500/10');
                }
            }

            if (badgeEl && quote.changePercent !== null) {
                const isPositive = quote.changePercent >= 0;
                
                if (dirEl) dirEl.textContent = isPositive ? '▲' : '▼';
                if (pctEl) pctEl.textContent = `${Math.abs(quote.changePercent).toFixed(2)}%`;

                badgeEl.className = `text-xs font-semibold ${isPositive ? 'text-green-400' : 'text-red-400'}`;
            }
        });
    }

    function flashElement(el, flashClass) {
        const originalClass = el.className;
        el.classList.add(flashClass);
        setTimeout(() => {
            el.className = originalClass;
        }, 1000);
    }

    function flashRow(row, flashClass) {
        row.classList.add(flashClass);
        setTimeout(() => {
            row.classList.remove(flashClass);
        }, 1000);
    }
</script>
@endsection
