@extends('layouts.layout')

@section('title', 'GoInvesting | Tu Dashboard de Mercados Financieros')

@section('content')
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
                                    <th class="py-4 px-5">Símbolo</th>
                                    <th class="py-4 px-5">Nombre</th>
                                    <th class="py-4 px-5 text-right">Precio</th>
                                    <th class="py-4 px-5 text-right">Cambio</th>
                                    <th class="py-4 px-5 text-right">Cambio %</th>
                                    <th class="py-4 px-5 text-center w-28">Tendencia (24h)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-900/50">
                                @if(empty($$tabKey))
                                    <tr>
                                        <td colspan="6" class="py-8 text-center text-sm text-slate-500">Cargando datos del mercado...</td>
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
                                        <tr class="hover:bg-slate-950/40 transition duration-150 group cursor-pointer" onclick="window.location.href='{{ route('assets.show', $quote['symbol']) }}'">
                                            <td class="py-4.5 px-5">
                                                <span class="font-extrabold text-sm text-white group-hover:text-indigo-400 transition">{{ $symbolClean }}</span>
                                            </td>
                                            <td class="py-4.5 px-5">
                                                <span class="text-xs text-slate-400 truncate block max-w-[150px]">{{ $quote['shortName'] }}</span>
                                            </td>
                                            <td class="py-4.5 px-5 text-right font-bold text-slate-100 text-sm">
                                                ${{ number_format($quote['price'] ?? 0, 2) }}
                                            </td>
                                            <td class="py-4.5 px-5 text-right font-semibold {{ $colorClass }} text-sm">
                                                {{ $isPositive ? '+' : '' }}{{ number_format($quote['change'] ?? 0, 2) }}
                                            </td>
                                            <td class="py-4.5 px-5 text-right">
                                                <span class="inline-block px-2.5 py-1 rounded-lg text-xs font-bold {{ $colorClass }} {{ $bgColorClass }}">
                                                    {{ $isPositive ? '+' : '' }}{{ number_format($quote['changePercent'] ?? 0, 2) }}%
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
                                <div class="flex items-center justify-between py-3 hover:bg-slate-900/30 px-2 rounded-xl transition duration-150 group cursor-pointer" onclick="window.location.href='{{ route('assets.show', $quote['symbol']) }}'">
                                    <div class="flex flex-col">
                                        <span class="font-extrabold text-sm text-slate-200 group-hover:text-indigo-400 transition">{{ $symbolClean }}</span>
                                        <span class="text-[10px] text-slate-500 truncate max-w-[120px]">{{ $quote['shortName'] }}</span>
                                    </div>
                                    <div class="text-right flex flex-col">
                                        <span class="text-sm font-bold text-slate-100">${{ number_format($quote['price'] ?? 0, 2) }}</span>
                                        <span class="text-xs font-semibold {{ $colorClass }}">{{ $isPositive ? '▲' : '▼' }} {{ number_format($quote['changePercent'] ?? 0, 2) }}%</span>
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
@endsection
