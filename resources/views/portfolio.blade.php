@extends('layouts.layout')

@section('title', 'Mi Portafolio Financiero | GoInvesting')

@section('content')
<div class="space-y-8">
    
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl lg:text-3xl font-extrabold text-white tracking-tight">Mi Portafolio</h1>
            <p class="text-sm text-slate-400">Control de fondos y posiciones integradas con tu cuenta de Alpaca Broker</p>
        </div>
        <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-400 hover:text-white transition">
            Volver a mercados
        </a>
    </div>

    @if(isset($error) && !isset($account))
        <!-- Error / Config State -->
        <div class="glass-panel rounded-2xl p-8 text-center space-y-4 max-w-xl mx-auto border-red-500/20 bg-red-500/5">
            <div class="w-12 h-12 rounded-full bg-red-500/10 flex items-center justify-center mx-auto text-red-400">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                </svg>
            </div>
            <h2 class="text-lg font-bold text-white">Configuración de Alpaca Pendiente</h2>
            <p class="text-sm text-slate-400 leading-relaxed">
                {{ $error }}
            </p>
            <div class="flex justify-center gap-4">
                <a href="{{ route('profile.edit') }}" class="px-4 py-2 rounded-xl text-xs font-extrabold bg-indigo-650 text-white hover:bg-indigo-550 transition shadow-md">
                    Configurar mis Credenciales
                </a>
            </div>
        </div>
    @endif

    <!-- General Info Alert for Beginners -->
    <div class="glass-panel rounded-2xl p-4 bg-indigo-950/15 border-indigo-500/10 text-xs text-slate-400 leading-relaxed flex gap-3 items-start">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-indigo-400 shrink-0 mt-0.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 1 1 .513 1.293l-.042.015-1.478.492a1 1 0 0 0-.674.933V15m3.75 2.25h.008v.008H13v-.008Z" />
        </svg>
        <div>
            <strong class="text-slate-200">Guía de Origen de Datos:</strong> Esta página muestra el estado en tiempo real de tu cuenta. Los saldos de capital y el poder de compra se consultan directamente de tu broker en <strong class="text-indigo-400">Alpaca API</strong>. El precio actual de tus posiciones abiertas y la valoración diaria se calculan enriqueciendo los datos con cotizaciones de <strong class="text-indigo-400">Yahoo Finance</strong> en tiempo real.
        </div>
    </div>

    @if(isset($account))
        @php
            $portfolioValue = (float)$account['portfolio_value'];
            $cash = (float)$account['cash'];
            $buyingPower = (float)$account['buying_power'];
            $initialMargin = (float)$account['initial_margin'];
            $isPaper = str_contains($account['currency'], 'USD') && (auth()->user()->alpaca_is_paper ?? config('services.alpaca.is_paper'));
        @endphp

        <!-- Account Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Net Asset Value -->
            <div class="glass-panel rounded-2xl p-6 shadow-xl space-y-2 relative bg-gradient-to-tr from-slate-900 to-indigo-950/30 group">
                <div class="flex items-center justify-between">
                    <span class="text-xs text-slate-500 font-bold uppercase tracking-wider block">Valor de Cartera (Net Worth)</span>
                    <!-- Tooltip -->
                    <div class="relative" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false" @click.away="open = false">
                        <svg @click="open = !open" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 text-slate-500 hover:text-slate-350 cursor-pointer">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 5.25h.008v.008H12v-.008Z" />
                        </svg>
                        <div x-show="open"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 translate-y-0"
                             x-transition:leave-end="opacity-0 translate-y-1"
                             style="display: none; width: 256px; max-width: 85vw;"
                             class="absolute bottom-full right-0 mb-2.5 z-50">
                            <div class="relative bg-slate-950/95 backdrop-blur-md text-slate-350 text-[11px] p-3 rounded-xl border border-slate-800/80 shadow-2xl leading-normal">
                                Es la suma de tu dinero en efectivo más el valor actual de mercado de todas tus acciones y criptomonedas abiertas.
                                <!-- Caret (Triangle) -->
                                <div class="absolute top-full right-1.5 -mt-[5px] w-2 h-2 bg-slate-950 border-r border-b border-slate-800/80 transform rotate-45"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <span class="text-3xl font-extrabold text-white block">${{ number_format($portfolioValue, 2) }}</span>
                <div class="flex items-center gap-1.5 mt-2">
                    <span class="text-xs px-2 py-0.5 rounded-md font-extrabold bg-indigo-500/10 text-indigo-400 border border-indigo-500/25">
                        {{ $isPaper ? 'Simulación (Paper)' : 'Dinero Real (Live)' }}
                    </span>
                    <span class="text-xs text-slate-500 font-medium">Moneda: {{ $account['currency'] }}</span>
                </div>
            </div>

            <!-- Cash & Capital -->
            <div class="glass-panel rounded-2xl p-6 shadow-xl space-y-2 relative group">
                <div class="flex items-center justify-between">
                    <span class="text-xs text-slate-500 font-bold uppercase tracking-wider block">Efectivo Disponible (Cash)</span>
                    <div class="relative" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false" @click.away="open = false">
                        <svg @click="open = !open" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 text-slate-500 hover:text-slate-350 cursor-pointer">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 5.25h.008v.008H12v-.008Z" />
                        </svg>
                        <div x-show="open"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 translate-y-0"
                             x-transition:leave-end="opacity-0 translate-y-1"
                             style="display: none; width: 256px; max-width: 85vw;"
                             class="absolute bottom-full right-0 mb-2.5 z-50">
                            <div class="relative bg-slate-950/95 backdrop-blur-md text-slate-350 text-[11px] p-3 rounded-xl border border-slate-800/80 shadow-2xl leading-normal">
                                Es el saldo líquido en tu cuenta que no está invertido. Puedes usarlo inmediatamente para abrir nuevas operaciones.
                                <!-- Caret (Triangle) -->
                                <div class="absolute top-full right-1.5 -mt-[5px] w-2 h-2 bg-slate-950 border-r border-b border-slate-800/80 transform rotate-45"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <span class="text-3xl font-extrabold text-slate-200 block">${{ number_format($cash, 2) }}</span>
                <span class="text-xs text-slate-500 block">Garantía Inicial: ${{ number_format($initialMargin, 2) }}</span>
            </div>

            <!-- Buying Power -->
            <div class="glass-panel rounded-2xl p-6 shadow-xl space-y-2 relative group">
                <div class="flex items-center justify-between">
                    <span class="text-xs text-slate-500 font-bold uppercase tracking-wider block">Poder de Compra (Buying Power)</span>
                    <div class="relative" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false" @click.away="open = false">
                        <svg @click="open = !open" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 text-slate-500 hover:text-slate-350 cursor-pointer">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 5.25h.008v.008H12v-.008Z" />
                        </svg>
                        <div x-show="open"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 translate-y-0"
                             x-transition:leave-end="opacity-0 translate-y-1"
                             style="display: none; width: 256px; max-width: 85vw;"
                             class="absolute bottom-full right-0 mb-2.5 z-50">
                            <div class="relative bg-slate-950/95 backdrop-blur-md text-slate-350 text-[11px] p-3 rounded-xl border border-slate-800/80 shadow-2xl leading-normal">
                                Es el límite máximo de capital que puedes emplear para comprar activos, incluyendo el margen de apalancamiento que te otorga el broker.
                                <!-- Caret (Triangle) -->
                                <div class="absolute top-full right-1.5 -mt-[5px] w-2 h-2 bg-slate-950 border-r border-b border-slate-800/80 transform rotate-45"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <span class="text-3xl font-extrabold text-indigo-400 block">${{ number_format($buyingPower, 2) }}</span>
                <span class="text-xs text-slate-500 block">Apalancamiento Máx: {{ $account['multiplier'] }}x</span>
            </div>
        </div>

        <!-- Limits Progress Bars -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Daily Limit -->
            <div class="glass-panel rounded-2xl p-5 shadow-lg space-y-3">
                <div class="flex items-center justify-between text-xs">
                    <span class="font-bold text-slate-400 uppercase">Gasto Diario Controlado</span>
                    <span class="font-extrabold text-slate-200">
                        ${{ number_format($dailySpent, 2) }} / 
                        {{ $dailyLimit ? '$' . number_format($dailyLimit, 2) : 'Sin Límite' }}
                    </span>
                </div>
                @php
                    $dailyPercent = $dailyLimit > 0 ? min(($dailySpent / $dailyLimit) * 100, 100) : 0;
                    $dailyBarColor = $dailyPercent >= 90 ? 'bg-red-500' : ($dailyPercent >= 70 ? 'bg-amber-500' : 'bg-indigo-500');
                @endphp
                <div class="w-full bg-slate-950 h-2.5 rounded-full overflow-hidden border border-slate-900">
                    <div class="h-full {{ $dailyBarColor }} transition-all duration-500" style="width: {{ $dailyPercent }}%"></div>
                </div>
                <div class="text-[10px] text-slate-500 flex justify-between">
                    <span>Límite establecido por el administrador para evitar pérdidas excesivas en un día.</span>
                    <span class="font-bold text-slate-400">{{ round($dailyPercent, 1) }}%</span>
                </div>
            </div>

            <!-- Weekly Limit -->
            <div class="glass-panel rounded-2xl p-5 shadow-lg space-y-3">
                <div class="flex items-center justify-between text-xs">
                    <span class="font-bold text-slate-400 uppercase">Gasto Semanal Controlado</span>
                    <span class="font-extrabold text-slate-200">
                        ${{ number_format($weeklySpent, 2) }} / 
                        {{ $weeklyLimit ? '$' . number_format($weeklyLimit, 2) : 'Sin Límite' }}
                    </span>
                </div>
                @php
                    $weeklyPercent = $weeklyLimit > 0 ? min(($weeklySpent / $weeklyLimit) * 100, 100) : 0;
                    $weeklyBarColor = $weeklyPercent >= 90 ? 'bg-red-500' : ($weeklyPercent >= 70 ? 'bg-amber-500' : 'bg-indigo-500');
                @endphp
                <div class="w-full bg-slate-950 h-2.5 rounded-full overflow-hidden border border-slate-900">
                    <div class="h-full {{ $weeklyBarColor }} transition-all duration-500" style="width: {{ $weeklyPercent }}%"></div>
                </div>
                <div class="text-[10px] text-slate-500 flex justify-between">
                    <span>Límite establecido para controlar el presupuesto de compra acumulado de la semana.</span>
                    <span class="font-bold text-slate-400">{{ round($weeklyPercent, 1) }}%</span>
                </div>
            </div>
        </div>

        <!-- Automated Trading Bot Panel -->
        <div class="glass-panel rounded-2xl p-6 shadow-xl space-y-6 bg-gradient-to-br from-indigo-950/20 via-slate-900 to-slate-900/50 border border-indigo-500/10">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="space-y-1">
                    <h2 class="text-lg font-extrabold text-white flex items-center gap-2">
                        <span class="relative flex h-3 w-3">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
                        </span>
                    <p class="text-xs text-slate-400">
                        Estrategia: <strong class="text-indigo-400 font-bold">Momentum / Caída Diaria</strong> (Compra caída diaria &le; {{ Auth::user()->bot_buy_threshold ?? -1.5 }}%, TP: +{{ Auth::user()->bot_take_profit ?? 2.0 }}%, SL: {{ Auth::user()->bot_stop_loss ?? -3.0 }}%). Tamaño Orden: <strong class="text-slate-350 font-semibold">${{ number_format(Auth::user()->bot_order_size ?? 500, 2) }}</strong> | Límite Inversión: <strong class="text-slate-300 font-bold">${{ number_format(Auth::user()->bot_max_investment ?? 500000, 2) }}</strong>.
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <form action="{{ route('portfolio.run-bot') }}" method="POST" class="inline">
                        @csrf
                        <input type="hidden" name="dry_run" value="1">
                        <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold bg-slate-800 text-slate-300 hover:bg-slate-700 hover:text-white transition">
                            Simular Ejecución (Dry Run)
                        </button>
                    </form>
                    <form action="{{ route('portfolio.run-bot') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="px-4 py-2 rounded-xl text-xs font-extrabold bg-indigo-600 text-white hover:bg-indigo-500 shadow-md shadow-indigo-600/20 hover:scale-[1.02] active:scale-[0.98] transition">
                            Ejecutar Bot Ahora
                        </button>
                    </form>
                </div>
            </div>

            <!-- Last Run Details -->
            <div class="border-t border-slate-900 pt-5 space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div class="flex items-center gap-2.5">
                        <span class="text-xs font-bold text-slate-400">Estado del Bot:</span>
                        @if($lastExecution)
                            <span class="text-xs px-2 py-0.5 rounded-md font-bold uppercase {{ $lastExecution->status === 'success' ? 'bg-green-500/10 text-green-400' : 'bg-red-500/10 text-red-400' }}">
                                {{ $lastExecution->status === 'success' ? 'Exitoso' : 'Error' }}
                            </span>
                            @if($lastExecution->is_dry_run)
                                <span class="text-xs px-2 py-0.5 rounded-md font-bold uppercase bg-amber-500/10 text-amber-400">
                                    Simulación
                                </span>
                            @endif
                        @else
                            <span class="text-xs text-slate-500">Nunca ejecutado</span>
                        @endif
                    </div>
                    <div class="text-xs text-slate-500 font-medium">
                        @if($lastExecution)
                            Última ejecución: <strong>{{ $lastExecution->started_at->format('d M Y, H:i:s') }}</strong> (Hace {{ $lastExecution->started_at->diffForHumans() }})
                        @endif
                    </div>
                </div>

                <!-- Last execution transactions summary -->
                @if($lastExecution)
                    <div class="bg-slate-950/40 border border-slate-900/60 rounded-xl p-4.5 space-y-2.5">
                        <div class="text-xs font-bold text-slate-350">Operaciones en la última ejecución:</div>
                        @php $execTrades = $lastExecution->trades; @endphp
                        @if($execTrades->isEmpty())
                            <div class="text-xs text-slate-500">No se realizaron compras ni ventas (el mercado no cumplió con las condiciones de la estrategia).</div>
                        @else
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                @foreach($execTrades as $t)
                                    <div class="flex items-center justify-between p-2.5 rounded-lg bg-slate-950/60 border border-slate-900">
                                        <div class="flex items-center gap-2">
                                            <span class="text-[10px] px-1.5 py-0.5 rounded font-extrabold uppercase {{ $t->side === 'buy' ? 'bg-green-500/10 text-green-400' : 'bg-red-500/10 text-red-400' }}">
                                                {{ $t->side === 'buy' ? 'Compra' : 'Venta' }}
                                            </span>
                                            <span class="text-xs font-bold text-slate-200">{{ $t->symbol }}</span>
                                        </div>
                                        <div class="text-right text-xs">
                                            <span class="text-slate-300 font-semibold">{{ $t->qty }} uds</span>
                                            <span class="text-slate-500 block text-[10px]">${{ number_format($t->price, 2) }}/ud</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            @if(session('bot_output'))
                <div class="space-y-2 border-t border-slate-900 pt-5">
                    <span class="text-xs font-bold text-slate-400 block">Resultado detallado (Consola):</span>
                    <pre class="bg-black/60 border border-slate-900 rounded-xl p-4 text-[11px] font-mono text-indigo-300 overflow-x-auto max-h-64 whitespace-pre-wrap leading-relaxed">{{ session('bot_output') }}</pre>
                </div>
            @elseif($lastExecution && $lastExecution->output)
                <details class="group border-t border-slate-900 pt-5">
                    <summary class="text-xs font-bold text-slate-400 hover:text-white cursor-pointer select-none flex items-center gap-1.5">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3.5 h-3.5 text-indigo-400 transition-transform group-open:rotate-90">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                        </svg>
                        Ver log de consola de la última ejecución
                    </summary>
                    <pre class="bg-black/60 border border-slate-900 rounded-xl p-4 mt-3 text-[11px] font-mono text-indigo-300 overflow-x-auto max-h-64 whitespace-pre-wrap leading-relaxed">{{ $lastExecution->output }}</pre>
                </details>
            @endif
        </div>

        <!-- Open Positions Table -->
        <div class="space-y-4">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-2">
                <div class="space-y-1">
                    <h2 class="text-lg font-extrabold text-white flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-indigo-400">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 0 0 6 16.5h2.25M3.75 3h-1.5m1.5 0v11.25m0 0h19.5m0 0h-2.25m-14.25-2.25h14.25m-14.25-3h14.25m-14.25-3H21" />
                        </svg>
                        Mis Posiciones Abiertas
                    </h2>
                    <p class="text-xs text-slate-400">
                        Representa las acciones o activos que posees en este momento. El bot los vigila para venderlos automáticamente cuando alcancen tus objetivos de ganancia (Take Profit) o límites de pérdida (Stop Loss).
                    </p>
                </div>
                <!-- Explainer -->
                <span class="text-[11px] text-slate-500 italic max-w-xs text-right hidden sm:block">
                    Haga clic en una fila para ver el gráfico interactivo del activo en tiempo real.
                </span>
            </div>

            <div class="glass-panel rounded-2xl overflow-hidden shadow-xl">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse min-w-[700px]">
                        <thead>
                            <tr class="border-b border-slate-900 text-xs font-bold uppercase tracking-wider text-slate-500 bg-[#070913]/30">
                                <th class="py-4 px-5">
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-slate-500">Símbolo</span>
                                        <div class="relative inline-block" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false" @click.away="open = false">
                                            <svg @click="open = !open" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3.5 h-3.5 text-slate-500 hover:text-slate-350 cursor-pointer">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 5.25h.008v.008H12v-.008Z" />
                                            </svg>
                                            <div x-show="open"
                                                 x-transition:enter="transition ease-out duration-200"
                                                 x-transition:enter-start="opacity-0 translate-y-1"
                                                 x-transition:enter-end="opacity-100 translate-y-0"
                                                 x-transition:leave="transition ease-in duration-150"
                                                 x-transition:leave-start="opacity-100 translate-y-0"
                                                 x-transition:leave-end="opacity-0 translate-y-1"
                                                 style="display: none; width: 220px; max-width: 85vw;"
                                                 class="absolute bottom-full left-0 mb-2.5 z-50 text-left normal-case">
                                                <div class="relative bg-slate-950/95 backdrop-blur-md text-slate-350 text-[10px] p-2.5 rounded-xl border border-slate-800/80 shadow-2xl leading-normal font-medium">
                                                    Código abreviado del activo en la bolsa de valores (ej: AAPL para Apple).
                                                    <div class="absolute top-full left-1.5 -mt-[5px] w-2 h-2 bg-slate-950 border-r border-b border-slate-800/80 transform rotate-45"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </th>
                                <th class="py-4 px-5 text-slate-500">Nombre</th>
                                <th class="py-4 px-5 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <span class="text-slate-500">Cantidad</span>
                                        <div class="relative inline-block" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false" @click.away="open = false">
                                            <svg @click="open = !open" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3.5 h-3.5 text-slate-500 hover:text-slate-350 cursor-pointer">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 5.25h.008v.008H12v-.008Z" />
                                            </svg>
                                            <div x-show="open"
                                                 x-transition:enter="transition ease-out duration-200"
                                                 x-transition:enter-start="opacity-0 translate-y-1"
                                                 x-transition:enter-end="opacity-100 translate-y-0"
                                                 x-transition:leave="transition ease-in duration-150"
                                                 x-transition:leave-start="opacity-100 translate-y-0"
                                                 x-transition:leave-end="opacity-0 translate-y-1"
                                                 style="display: none; width: 220px; max-width: 85vw;"
                                                 class="absolute bottom-full right-0 mb-2.5 z-50 text-left normal-case">
                                                <div class="relative bg-slate-950/95 backdrop-blur-md text-slate-350 text-[10px] p-2.5 rounded-xl border border-slate-800/80 shadow-2xl leading-normal font-medium">
                                                    Número total de unidades o acciones de este activo que posees actualmente.
                                                    <div class="absolute top-full right-1.5 -mt-[5px] w-2 h-2 bg-slate-950 border-r border-b border-slate-800/80 transform rotate-45"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </th>
                                <th class="py-4 px-5 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <span class="text-slate-500">Precio Medio</span>
                                        <div class="relative inline-block" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false" @click.away="open = false">
                                            <svg @click="open = !open" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3.5 h-3.5 text-slate-500 hover:text-slate-350 cursor-pointer">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 5.25h.008v.008H12v-.008Z" />
                                            </svg>
                                            <div x-show="open"
                                                 x-transition:enter="transition ease-out duration-200"
                                                 x-transition:enter-start="opacity-0 translate-y-1"
                                                 x-transition:enter-end="opacity-100 translate-y-0"
                                                 x-transition:leave="transition ease-in duration-150"
                                                 x-transition:leave-start="opacity-100 translate-y-0"
                                                 x-transition:leave-end="opacity-0 translate-y-1"
                                                 style="display: none; width: 220px; max-width: 85vw;"
                                                 class="absolute bottom-full right-0 mb-2.5 z-50 text-left normal-case">
                                                <div class="relative bg-slate-950/95 backdrop-blur-md text-slate-350 text-[10px] p-2.5 rounded-xl border border-slate-800/80 shadow-2xl leading-normal font-medium">
                                                    Precio promedio al que compraste estas acciones en tu cartera.
                                                    <div class="absolute top-full right-1.5 -mt-[5px] w-2 h-2 bg-slate-950 border-r border-b border-slate-800/80 transform rotate-45"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </th>
                                <th class="py-4 px-5 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <span class="text-slate-500">Precio Actual</span>
                                        <div class="relative inline-block" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false" @click.away="open = false">
                                            <svg @click="open = !open" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3.5 h-3.5 text-slate-500 hover:text-slate-350 cursor-pointer">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 5.25h.008v.008H12v-.008Z" />
                                            </svg>
                                            <div x-show="open"
                                                 x-transition:enter="transition ease-out duration-200"
                                                 x-transition:enter-start="opacity-0 translate-y-1"
                                                 x-transition:enter-end="opacity-100 translate-y-0"
                                                 x-transition:leave="transition ease-in duration-150"
                                                 x-transition:leave-start="opacity-100 translate-y-0"
                                                 x-transition:leave-end="opacity-0 translate-y-1"
                                                 style="display: none; width: 220px; max-width: 85vw;"
                                                 class="absolute bottom-full right-0 mb-2.5 z-50 text-left normal-case">
                                                <div class="relative bg-slate-950/95 backdrop-blur-md text-slate-350 text-[10px] p-2.5 rounded-xl border border-slate-800/80 shadow-2xl leading-normal font-medium">
                                                    Precio de cotización del activo hoy en el mercado en tiempo real.
                                                    <div class="absolute top-full right-1.5 -mt-[5px] w-2 h-2 bg-slate-950 border-r border-b border-slate-800/80 transform rotate-45"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </th>
                                <th class="py-4 px-5 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <span class="text-slate-500">Costo Total</span>
                                        <div class="relative inline-block" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false" @click.away="open = false">
                                            <svg @click="open = !open" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3.5 h-3.5 text-slate-500 hover:text-slate-350 cursor-pointer">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 5.25h.008v.008H12v-.008Z" />
                                            </svg>
                                            <div x-show="open"
                                                 x-transition:enter="transition ease-out duration-200"
                                                 x-transition:enter-start="opacity-0 translate-y-1"
                                                 x-transition:enter-end="opacity-100 translate-y-0"
                                                 x-transition:leave="transition ease-in duration-150"
                                                 x-transition:leave-start="opacity-100 translate-y-0"
                                                 x-transition:leave-end="opacity-0 translate-y-1"
                                                 style="display: none; width: 220px; max-width: 85vw;"
                                                 class="absolute bottom-full right-0 mb-2.5 z-50 text-left normal-case">
                                                <div class="relative bg-slate-950/95 backdrop-blur-md text-slate-350 text-[10px] p-2.5 rounded-xl border border-slate-800/80 shadow-2xl leading-normal font-medium">
                                                    Total del capital invertido en esta posición (Cantidad × Precio Medio).
                                                    <div class="absolute top-full right-1.5 -mt-[5px] w-2 h-2 bg-slate-950 border-r border-b border-slate-800/80 transform rotate-45"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </th>
                                <th class="py-4 px-5 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <span class="text-slate-500">Valor Actual</span>
                                        <div class="relative inline-block" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false" @click.away="open = false">
                                            <svg @click="open = !open" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3.5 h-3.5 text-slate-500 hover:text-slate-350 cursor-pointer">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 5.25h.008v.008H12v-.008Z" />
                                            </svg>
                                            <div x-show="open"
                                                 x-transition:enter="transition ease-out duration-200"
                                                 x-transition:enter-start="opacity-0 translate-y-1"
                                                 x-transition:enter-end="opacity-100 translate-y-0"
                                                 x-transition:leave="transition ease-in duration-150"
                                                 x-transition:leave-start="opacity-100 translate-y-0"
                                                 x-transition:leave-end="opacity-0 translate-y-1"
                                                 style="display: none; width: 220px; max-width: 85vw;"
                                                 class="absolute bottom-full right-0 mb-2.5 z-50 text-left normal-case">
                                                <div class="relative bg-slate-950/95 backdrop-blur-md text-slate-350 text-[10px] p-2.5 rounded-xl border border-slate-800/80 shadow-2xl leading-normal font-medium">
                                                    Valorización actual de tu posición al precio de mercado (Cantidad × Precio Actual).
                                                    <div class="absolute top-full right-1.5 -mt-[5px] w-2 h-2 bg-slate-950 border-r border-b border-slate-800/80 transform rotate-45"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </th>
                                <th class="py-4 px-5 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <span class="text-slate-500">G/P no realizado</span>
                                        <div class="relative inline-block" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false" @click.away="open = false">
                                            <svg @click="open = !open" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3.5 h-3.5 text-slate-500 hover:text-slate-350 cursor-pointer">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 5.25h.008v.008H12v-.008Z" />
                                            </svg>
                                            <div x-show="open"
                                                 x-transition:enter="transition ease-out duration-200"
                                                 x-transition:enter-start="opacity-0 translate-y-1"
                                                 x-transition:enter-end="opacity-100 translate-y-0"
                                                 x-transition:leave="transition ease-in duration-150"
                                                 x-transition:leave-start="opacity-100 translate-y-0"
                                                 x-transition:leave-end="opacity-0 translate-y-1"
                                                 style="display: none; width: 220px; max-width: 85vw;"
                                                 class="absolute bottom-full right-0 mb-2.5 z-50 text-left normal-case">
                                                <div class="relative bg-slate-950/95 backdrop-blur-md text-slate-350 text-[10px] p-2.5 rounded-xl border border-slate-800/80 shadow-2xl leading-normal font-medium">
                                                    Ganancia o pérdida acumulada sobre el papel. Solo se consolida cuando decides vender el activo.
                                                    <div class="absolute top-full right-1.5 -mt-[5px] w-2 h-2 bg-slate-950 border-r border-b border-slate-800/80 transform rotate-45"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-900/50">
                            @if(empty($positions))
                                <tr>
                                    <td colspan="8" class="py-8 text-center text-sm text-slate-500">No tienes posiciones abiertas en este momento.</td>
                                </tr>
                            @else
                                @foreach($positions as $pos)
                                    @php
                                        $isPositive = $pos['unrealized_pl'] >= 0;
                                        $colorClass = $isPositive ? 'text-green-400' : 'text-red-400';
                                        $bgColorClass = $isPositive ? 'bg-green-500/10' : 'bg-red-500/10';
                                    @endphp
                                    <tr class="hover:bg-slate-950/40 transition duration-150 group cursor-pointer" onclick="window.location.href='{{ route('assets.show', $pos['symbol']) }}'">
                                        <td class="py-4.5 px-5">
                                            <span class="font-extrabold text-sm text-white group-hover:text-indigo-400 transition">{{ $pos['symbol'] }}</span>
                                        </td>
                                        <td class="py-4.5 px-5">
                                            <span class="text-xs text-slate-400 block max-w-[120px] truncate">{{ $pos['name'] }}</span>
                                        </td>
                                        <td class="py-4.5 px-5 text-right font-semibold text-slate-200 text-sm">
                                            {{ $pos['qty'] }}
                                        </td>
                                        <td class="py-4.5 px-5 text-right font-medium text-slate-400 text-sm">
                                            ${{ number_format($pos['avg_entry_price'], 2) }}
                                        </td>
                                        <td class="py-4.5 px-5 text-right font-bold text-slate-200 text-sm">
                                            ${{ number_format($pos['current_price'], 2) }}
                                        </td>
                                        <td class="py-4.5 px-5 text-right font-medium text-slate-400 text-sm">
                                            ${{ number_format($pos['cost_basis'], 2) }}
                                        </td>
                                        <td class="py-4.5 px-5 text-right font-bold text-slate-100 text-sm">
                                            ${{ number_format($pos['market_value'], 2) }}
                                        </td>
                                        <td class="py-4.5 px-5 text-right">
                                            <div class="flex flex-col items-end">
                                                <span class="text-sm font-extrabold {{ $colorClass }}">
                                                    {{ $isPositive ? '+' : '' }}${{ number_format($pos['unrealized_pl'], 2) }}
                                                </span>
                                                <span class="inline-block px-1.5 py-0.5 rounded text-[10px] font-bold {{ $colorClass }} {{ $bgColorClass }} mt-0.5">
                                                    {{ $isPositive ? '+' : '' }}{{ number_format($pos['unrealized_plpc'], 2) }}%
                                                </span>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Recent Trades History -->
        <div class="space-y-4">
            <h2 class="text-lg font-extrabold text-white flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-indigo-400">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                Historial de Operaciones Recientes
            </h2>

            <div class="glass-panel rounded-2xl overflow-hidden shadow-xl">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse min-w-[700px]">
                        <thead>
                            <tr class="border-b border-slate-900 text-xs font-bold uppercase tracking-wider text-slate-500 bg-[#070913]/30">
                                <th class="py-4 px-5">Fecha</th>
                                <th class="py-4 px-5">Tipo</th>
                                <th class="py-4 px-5">Origen</th>
                                <th class="py-4 px-5">Símbolo</th>
                                <th class="py-4 px-5 text-right">Cantidad</th>
                                <th class="py-4 px-5 text-right">Precio Ejecución</th>
                                <th class="py-4 px-5 text-right">Total</th>
                                <th class="py-4 px-5 text-right">Modo</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-900/50">
                            @if($recentTrades->isEmpty())
                                <tr>
                                    <td colspan="8" class="py-8 text-center text-sm text-slate-500">Aún no se han registrado transacciones en esta cuenta.</td>
                                </tr>
                            @else
                                @foreach($recentTrades as $trade)
                                    @php
                                        $isBuy = $trade->side === 'buy';
                                        $tradeTotal = $trade->qty * $trade->price;
                                    @endphp
                                    <tr class="hover:bg-slate-950/20 transition">
                                        <td class="py-3.5 px-5 text-xs text-slate-400 font-medium">
                                            {{ $trade->created_at->format('d/m/Y, H:i') }}
                                        </td>
                                        <td class="py-3.5 px-5">
                                            <span class="text-[10px] px-2 py-0.5 rounded font-extrabold uppercase {{ $isBuy ? 'bg-green-500/10 text-green-400' : 'bg-red-500/10 text-red-400' }}">
                                                {{ $isBuy ? 'Compra' : 'Venta' }}
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-5 text-xs text-slate-400 font-bold">
                                            {{ $trade->bot_execution_id ? 'Bot Automático' : 'Manual' }}
                                        </td>
                                        <td class="py-3.5 px-5 text-sm font-bold text-white">
                                            {{ $trade->symbol }}
                                        </td>
                                        <td class="py-3.5 px-5 text-right text-xs text-slate-300 font-semibold">
                                            {{ $trade->qty }}
                                        </td>
                                        <td class="py-3.5 px-5 text-right text-xs text-slate-400">
                                            ${{ number_format($trade->price, 2) }}
                                        </td>
                                        <td class="py-3.5 px-5 text-right text-xs text-slate-200 font-bold">
                                            ${{ number_format($tradeTotal, 2) }}
                                        </td>
                                        <td class="py-3.5 px-5 text-right">
                                            <span class="text-[10px] px-1.5 py-0.5 rounded font-medium uppercase {{ $trade->is_dry_run ? 'bg-amber-500/10 text-amber-400' : 'bg-green-500/10 text-green-400' }}">
                                                {{ $trade->is_dry_run ? 'Simulado' : 'Real' }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

</div>
@endsection
