@extends('layouts.layout')

@section('title', 'Mi Portafolio Financiero | GoInvesting')

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
    $isPaper = false;
    if (isset($account)) {
        $isPaper = str_contains($account['currency'] ?? 'USD', 'USD') && (auth()->user()->alpaca_is_paper ?? config('services.alpaca.is_paper'));
    }

    $executionSummary = 'No se registraron ejecuciones recientes en este modo.';
    if (isset($lastExecution)) {
        $hasTrades = $lastExecution->trades && $lastExecution->trades->isNotEmpty();
        if ($hasTrades) {
            $tradeActions = [];
            foreach ($lastExecution->trades as $trade) {
                $tradeActions[] = ($trade->side === 'buy' ? 'Compró' : 'Vendió') . " {$trade->qty} uds de " . ($friendlyNames[$trade->symbol] ?? $trade->symbol) . " a $" . number_format($trade->price, 2);
            }
            $executionSummary = implode(', ', $tradeActions);
        } else {
            $logLines = explode("\n", $lastExecution->output);
            $reason = 'No operó: los activos evaluados no cumplieron los criterios de compra (caída de precio) ni de venta (Take Profit / Stop Loss).';
            foreach ($logLines as $line) {
                if (str_contains($line, 'cancelada') && str_contains($line, 'gasto')) {
                    $reason = 'No operó: Se superó el límite de gasto diario o semanal establecido.';
                    break;
                } elseif (str_contains($line, 'insuficiente')) {
                    $reason = 'No operó: Poder de compra o efectivo insuficiente en el broker.';
                    break;
                }
            }
            $executionSummary = $reason;
        }
    }
@endphp
<div class="space-y-8">
    
    <!-- Alerts -->
    @if(session('success'))
        <div class="glass-panel border-green-500/20 bg-green-500/5 rounded-2xl p-4 text-sm text-green-400 font-bold flex items-center gap-3">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1 3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z" />
            </svg>
            <div>
                {!! session('success') !!}
            </div>
        </div>
    @endif

    @if(session('error') || $errors->any())
        <div class="glass-panel border-red-500/20 bg-red-500/5 rounded-2xl p-4 text-sm text-red-400 font-bold flex items-center gap-3">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
            </svg>
            <div>
                <div>{!! session('error') ?? $errors->first() !!}</div>
                @if(str_contains(session('error') ?? '', 'Real') || str_contains(session('error') ?? '', 'Live') || str_contains($errors->first() ?? '', 'Real') || str_contains($errors->first() ?? '', 'Live'))
                    <button type="button" x-data @click="$dispatch('open-alpaca-support')" class="mt-3 text-xs text-indigo-400 hover:text-indigo-300 font-extrabold underline flex items-center gap-1 cursor-pointer">
                        ¿Necesitas ayuda? Abre nuestro Asistente de Contacto con Alpaca
                    </button>
                @endif
            </div>
        </div>
    @endif

    @if(session('bot_output'))
        @php
            $output = session('bot_output');
            $logLines = explode("\n", $output);
            $operations = [];
            $warnings = [];
            $hasBuysOrSells = false;

            foreach ($logLines as $line) {
                $line = trim($line);
                if (empty($line)) continue;

                // Detect successful real or simulation buy
                if (str_contains($line, 'Compra ejecutada') || str_contains($line, 'Orden de compra enviada')) {
                    $operations[] = [
                        'type' => 'buy',
                        'raw' => ltrim($line, '-> ')
                    ];
                    $hasBuysOrSells = true;
                }
                // Detect successful real or simulation sell
                elseif (str_contains($line, 'Venta ejecutada') || str_contains($line, 'Orden de venta enviada')) {
                    $operations[] = [
                        'type' => 'sell',
                        'raw' => ltrim($line, '-> ')
                    ];
                    $hasBuysOrSells = true;
                }
                // Detect cancellation reasons
                elseif (str_contains($line, 'cancelada:') || str_contains($line, 'insuficiente')) {
                    $warnings[] = ltrim($line, '-> ');
                }
            }
        @endphp

        <div x-data="{ showConsole: false }" class="glass-panel border-indigo-500/20 bg-indigo-500/5 rounded-2xl p-5 space-y-4">
            <div class="flex items-center justify-between border-b border-indigo-500/10 pb-3">
                <span class="text-xs font-black text-indigo-300 uppercase tracking-wider flex items-center gap-1.5">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 text-indigo-400">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25V9m10.5 0a2.25 2.25 0 0 1 2.25 2.25v6.75a2.25 2.25 0 0 1-2.25 2.25h-10.5a2.25 2.25 0 0 1-2.25-2.25v-6.75a2.25 2.25 0 0 1 2.25-2.25M10.5 9V5.25a.75.75 0 0 0-.75-.75h-3a.75.75 0 0 0-.75.75V9" />
                    </svg>
                    Resumen de Ejecución del Bot
                </span>
                <button type="button" onclick="this.parentElement.parentElement.remove()" class="text-slate-400 hover:text-slate-200 text-xs font-bold cursor-pointer">
                    Cerrar
                </button>
            </div>

            <!-- Friendly Message -->
            @if($hasBuysOrSells)
                <div class="p-4 bg-emerald-500/10 rounded-xl border border-emerald-500/20 text-xs space-y-2">
                    <span class="font-extrabold text-emerald-400 block uppercase tracking-wide flex items-center gap-1.5">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                        </span>
                        ¡Operación Realizada con Éxito!
                    </span>
                    <ul class="space-y-1.5 text-slate-300 list-disc list-inside font-medium">
                        @foreach($operations as $op)
                            <li>{{ $op['raw'] }}</li>
                        @endforeach
                    </ul>
                </div>
            @else
                <div class="p-4 bg-slate-950/80 rounded-xl border border-slate-900/60 text-xs space-y-2">
                    <span class="font-extrabold text-slate-400 block uppercase tracking-wide">Sin operaciones en este ciclo</span>
                    <div class="text-slate-300 leading-relaxed font-medium">
                        @if(!empty($warnings))
                            No se realizaron transacciones debido a las siguientes restricciones/alertas detectadas:
                            <ul class="mt-2 space-y-1 text-rose-300 list-disc list-inside font-semibold">
                                @foreach($warnings as $warn)
                                    <li>{{ $warn }}</li>
                                @endforeach
                            </ul>
                        @else
                            El bot finalizó correctamente sin realizar transacciones comerciales porque ningún activo analizado cumplió las condiciones necesarias para comprar (caída de precio suficiente) o vender (margen de ganancia o parada de pérdidas alcanzados).
                        @endif
                    </div>
                </div>
            @endif

            <!-- Toggle raw console output -->
            <div class="pt-1">
                <button type="button" @click="showConsole = !showConsole" class="text-xs text-indigo-400 hover:text-indigo-300 font-extrabold underline flex items-center gap-1.5 cursor-pointer">
                    <span x-text="showConsole ? 'Ocultar consola detallada' : 'Ver consola detallada de la ejecución'">Ver consola detallada de la ejecución</span>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3.5 h-3.5 transition-transform duration-200" :class="showConsole ? 'rotate-180' : ''">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                    </svg>
                </button>
            </div>

            <div x-show="showConsole" x-cloak class="mt-3" x-transition>
                <pre class="bg-black/60 border border-slate-900/60 rounded-xl p-4 text-[10px] font-mono text-indigo-300 overflow-x-auto max-h-64 whitespace-pre-wrap leading-relaxed">{{ session('bot_output') }}</pre>
            </div>
        </div>
    @endif

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                <div class="flex items-center gap-3 flex-wrap">
                    <h1 class="text-2xl lg:text-3xl font-extrabold text-white tracking-tight">Mi Portafolio</h1>
                    @if(isset($lastExecution))
                        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl text-xs font-semibold bg-slate-950/80 border border-slate-900/60 text-slate-400">
                            <span class="flex h-2 w-2 relative">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full {{ $lastExecution->status === 'success' ? 'bg-emerald-400' : 'bg-rose-400' }} opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 {{ $lastExecution->status === 'success' ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                            </span>
                            <span>
                                Bot ejecutado hace {{ $lastExecution->started_at->timezone('Europe/Madrid')->diffForHumans() }}
                            </span>
                        </div>
                    @endif
                </div>
                @if(isset($account))
                    <form action="{{ route('portfolio.toggle-paper') }}" method="POST" class="inline-block">
                        @csrf
                        <div class="inline-flex p-1 rounded-xl bg-slate-950/80 border border-slate-900/60 shadow-inner">
                            <button type="submit" 
                                    @if($isPaper) disabled @endif 
                                    class="px-4 py-1.5 rounded-lg text-xs font-bold transition duration-200 flex items-center gap-1.5 cursor-pointer disabled:cursor-default {{ $isPaper ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/10 animate-paper-glow' : 'text-slate-400 hover:text-slate-200' }}">
                                <span class="w-1.5 h-1.5 rounded-full bg-indigo-400 {{ $isPaper ? 'animate-pulse' : 'opacity-40' }}"></span>
                                Simulación (Paper)
                            </button>
                            <button type="submit" 
                                    @if(!$isPaper) disabled @endif 
                                    class="px-4 py-1.5 rounded-lg text-xs font-bold transition duration-200 flex items-center gap-1.5 cursor-pointer disabled:cursor-default {{ !$isPaper ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/10 animate-live-glow' : 'text-slate-400 hover:text-slate-200' }}">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 {{ !$isPaper ? 'animate-pulse' : 'opacity-40' }}"></span>
                                Real (Live)
                            </button>
                        </div>
                    </form>
                @endif
            </div>
            <p class="text-sm text-slate-400 mt-2 flex flex-wrap items-center gap-2">
                <span>Control de fondos y posiciones integradas con tu cuenta de Alpaca Broker</span>
                @if(isset($statusPaper) && isset($statusLive))
                    <span class="inline-flex items-center gap-3 text-[10px] font-bold text-slate-500 bg-slate-950/40 px-2.5 py-1 rounded-lg border border-slate-900/60 shadow-inner">
                        <span class="flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full {{ $statusPaper === 'connected' ? 'bg-green-500' : ($statusPaper === 'failed' ? 'bg-red-500' : 'bg-slate-700') }}"></span>
                            Simulación: <span class="{{ $statusPaper === 'connected' ? 'text-green-400' : ($statusPaper === 'failed' ? 'text-red-400' : 'text-slate-500') }}">{{ $statusPaper === 'connected' ? 'Activa' : ($statusPaper === 'failed' ? 'Error' : 'Sin configurar') }}</span>
                        </span>
                        <span class="flex items-center gap-1 border-l border-slate-900/80 pl-3">
                            <span class="w-1.5 h-1.5 rounded-full {{ $statusLive === 'connected' ? 'bg-green-500' : ($statusLive === 'failed' ? 'bg-red-500' : 'bg-slate-700') }}"></span>
                            Real: <span class="{{ $statusLive === 'connected' ? 'text-green-400' : ($statusLive === 'failed' ? 'text-red-400' : 'text-slate-500') }}">{{ $statusLive === 'connected' ? 'Activa' : ($statusLive === 'failed' ? 'Error' : 'Sin configurar') }}</span>
                        </span>
                    </span>
                @endif
            </p>
        </div>

        @if(isset($account))
            <!-- Ejecutar Bot Ahora Button -->
            <div class="flex items-center gap-3 shrink-0">
                <form action="{{ route('portfolio.run-bot') }}" method="POST" class="inline m-0">
                    @csrf
                    <input type="hidden" name="active_tab" value="{{ session('active_tab', request()->get('tab', 'portfolio_value')) }}">
                    <input type="hidden" name="mode" value="{{ $isPaper ? 'paper' : 'live' }}">
                    <button type="submit" 
                            @if(!$isPaper && !Auth::user()->alpaca_live_consent) disabled title="Debes autorizar el bot real primero" @endif
                            class="px-4 py-2.5 rounded-xl text-xs font-extrabold transition duration-150 cursor-pointer disabled:opacity-45 disabled:cursor-not-allowed flex items-center gap-1.5 {{ $isPaper ? 'bg-indigo-650 hover:bg-indigo-550 text-white shadow-md shadow-indigo-650/20 hover:scale-[1.02] active:scale-[0.98]' : 'bg-emerald-650 hover:bg-emerald-550 text-white shadow-md shadow-emerald-650/20 hover:scale-[1.02] active:scale-[0.98]' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.348a1.125 1.125 0 0 1 0 1.971l-11.54 6.347a1.125 1.125 0 0 1-1.667-.985V5.653Z" />
                        </svg>
                        Ejecutar Bot Ahora
                    </button>
                </form>
            </div>
        @endif
    </div>

    <!-- Compact Bot Status Bar -->
    @if(isset($account))
        <div class="glass-panel rounded-2xl p-4 bg-[#060e15]/20 border border-slate-900/60 shadow-lg flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div class="flex flex-wrap items-center gap-4">
                <!-- Consent Status or Paper Status -->
                <div class="flex items-center gap-2">
                    @if(!$isPaper)
                        @if(Auth::user()->alpaca_live_consent)
                            <span class="text-[10px] font-extrabold text-emerald-400 bg-emerald-950/20 px-2.5 py-1.5 rounded-xl border border-emerald-500/25 animate-pulse uppercase tracking-wider">
                                ● Bot Real Autorizado
                            </span>
                            <form action="{{ route('portfolio.toggle-live-consent') }}" method="POST" class="m-0 inline" onsubmit="return confirm('¿Estás seguro de que deseas revocar la autorización del bot real? Dejará de operar inmediatamente con tu dinero real.')">
                                @csrf
                                <button type="submit" class="px-2.5 py-1.5 rounded-xl text-[9px] font-black uppercase tracking-wider transition-all duration-200 cursor-pointer shadow-md bg-rose-950/40 text-red-400 border border-rose-500/30 hover:bg-rose-500/20">
                                    Revocar
                                </button>
                            </form>
                        @else
                            <span class="text-[10px] font-extrabold text-rose-400 bg-rose-950/20 px-2.5 py-1.5 rounded-xl border border-rose-500/25 uppercase tracking-wider">
                                ○ Bot Real Sin Autorización
                            </span>
                            <form action="{{ route('portfolio.toggle-live-consent') }}" method="POST" class="m-0 inline" onsubmit="return confirm('ATENCIÓN: Estás a punto de autorizar al bot automático a realizar operaciones con DINERO REAL en tu cuenta de Alpaca. ¿Estás seguro de que deseas proceder?')">
                                @csrf
                                <button type="submit" class="px-2.5 py-1.5 rounded-xl text-[9px] font-black uppercase tracking-wider transition-all duration-200 cursor-pointer shadow-md bg-emerald-600 hover:bg-emerald-500 text-white shadow-emerald-650/10">
                                    Autorizar
                                </button>
                            </form>
                        @endif
                    @else
                        <span class="text-[10px] font-extrabold text-indigo-400 bg-indigo-950/20 px-2.5 py-1.5 rounded-xl border border-indigo-500/25 uppercase tracking-wider">
                            ● Bot de Simulación Activo
                        </span>
                    @endif
                </div>

                <div class="hidden lg:block border-l border-slate-900 h-6"></div>

                <!-- Last Execution Info -->
                @if(isset($lastExecution))
                    <div class="flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full {{ $lastExecution->status === 'success' ? 'bg-emerald-500' : 'bg-rose-500' }} animate-pulse"></span>
                        <span class="text-xs text-slate-400 font-semibold">
                            Último ciclo: <span class="text-slate-200 font-bold">{{ $lastExecution->started_at->timezone('Europe/Madrid')->format('d/m H:i:s') }}</span>
                        </span>
                        <span class="text-xs text-slate-500">|</span>
                        <span class="text-xs italic text-indigo-300 font-semibold truncate max-w-[280px] xl:max-w-[400px]" title="{{ $executionSummary }}">
                            {{ $executionSummary }}
                        </span>
                    </div>
                @endif
            </div>

            <!-- Realized profit/loss or Mode Label -->
            <div class="flex items-center gap-2 shrink-0">
                @if(!$isPaper)
                    <span class="text-[10px] text-slate-500 font-bold uppercase tracking-wider">G/P Real Realizada:</span>
                    <span class="text-sm font-mono font-extrabold {{ $totalRealRealizedPL >= 0 ? 'text-emerald-400 bg-emerald-950/10' : 'text-rose-400 bg-rose-950/10' }} px-2 py-0.5 rounded border {{ $totalRealRealizedPL >= 0 ? 'border-emerald-500/10' : 'border-rose-500/10' }}">
                        {{ $totalRealRealizedPL >= 0 ? '+' : '' }}${{ number_format($totalRealRealizedPL, 2) }}
                    </span>
                @else
                    <span class="text-[10px] text-slate-500 font-bold uppercase tracking-wider">Modo Simulación (Demo)</span>
                @endif
            </div>
        </div>
    @endif

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



    @if(isset($account))
        @php
            $portfolioValue = (float)$account['portfolio_value'];
            $cash = (float)$account['cash'];
            $buyingPower = (float)$account['buying_power'];
            $initialMargin = (float)$account['initial_margin'];
            $isPaper = str_contains($account['currency'], 'USD') && (auth()->user()->alpaca_is_paper ?? config('services.alpaca.is_paper'));

            $totalUnrealizedPL = 0.0;
            $totalCostBasis = 0.0;
            $totalMarketValue = 0.0;
            $hasPositions = !empty($positions);
            foreach($positions as $pos) {
                $totalUnrealizedPL += $pos['unrealized_pl'];
                $totalCostBasis += $pos['cost_basis'];
                $totalMarketValue += $pos['market_value'];
            }
            $totalPLPercent = $totalCostBasis > 0 ? ($totalUnrealizedPL / $totalCostBasis) * 100 : 0.0;
            $isWinning = $totalUnrealizedPL >= 0;
        @endphp

        <div x-data="{ 
            activeTab: '{{ session('active_tab', request()->get('tab')) }}' || sessionStorage.getItem('portfolio_active_tab') || 'portfolio_value' 
        }" 
        x-init="sessionStorage.setItem('portfolio_active_tab', activeTab)"
        class="space-y-6">
            <!-- Tabs Menu (Primary Navigation - Redesigned to match image) -->
            <div class="flex p-2 rounded-2xl bg-slate-950/80 border border-slate-900/60 shadow-2xl overflow-x-auto pb-2 scrollbar-none gap-3 mb-4">
                
                <!-- Valor de mi cartera -->
                <button @click="activeTab = 'portfolio_value'; sessionStorage.setItem('portfolio_active_tab', 'portfolio_value')" 
                        :class="activeTab === 'portfolio_value' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/20 font-extrabold' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-900/50 font-bold'"
                        class="px-5 py-3 rounded-xl text-xs uppercase tracking-wider transition-all duration-200 flex items-center gap-3 shrink-0 cursor-pointer focus:outline-none">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor" class="w-6 h-6 shrink-0" style="width: 24px; height: 24px;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a2.25 2.25 0 0 0-2.25-2.25H15a3 3 0 1 1-6 0H5.25A2.25 2.25 0 0 0 3 12m18 0v6a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 18v-6m18 0V9M3 12V9m18 0a2.25 2.25 0 0 0-2.25-2.25H5.25A2.25 2.25 0 0 0 3 9m18 0V6a2.25 2.25 0 0 0-2.25-2.25H5.25A2.25 2.25 0 0 0 3 6v3" />
                    </svg>
                    <div class="flex flex-col items-start text-left leading-tight shrink-0">
                        <span class="text-[9px] font-bold tracking-wider opacity-85">VALOR DE</span>
                        <span class="text-xs font-black tracking-wide">MI CARTERA</span>
                    </div>
                </button>


                <!-- Mis Acciones -->
                <button @click="activeTab = 'positions'; sessionStorage.setItem('portfolio_active_tab', 'positions')" 
                        :class="activeTab === 'positions' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/20 font-extrabold' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-900/50 font-bold'"
                        class="px-5 py-3 rounded-xl text-xs uppercase tracking-wider transition-all duration-200 flex items-center gap-3 shrink-0 cursor-pointer focus:outline-none">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor" class="w-6 h-6 shrink-0" style="width: 24px; height: 24px;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
                    </svg>
                    <div class="flex flex-col items-start text-left leading-tight shrink-0">
                        <span class="text-[9px] font-bold tracking-wider opacity-85">MIS</span>
                        <span class="text-xs font-black tracking-wide">ACCIONES</span>
                    </div>
                </button>

                @if(!$isPaper)
                <!-- Nivel de Riesgo Sugerido -->
                <button @click="activeTab = 'risk_level'; sessionStorage.setItem('portfolio_active_tab', 'risk_level')" 
                        :class="activeTab === 'risk_level' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/20 font-extrabold' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-900/50 font-bold'"
                        class="px-5 py-3 rounded-xl text-xs uppercase tracking-wider transition-all duration-200 flex items-center gap-3 shrink-0 cursor-pointer focus:outline-none">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor" class="w-6 h-6 shrink-0" style="width: 24px; height: 24px;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                    </svg>
                    <div class="flex flex-col items-start text-left leading-tight shrink-0">
                        <span class="text-[9px] font-bold tracking-wider opacity-85">NIVEL DE</span>
                        <span class="text-xs font-black tracking-wide">RIESGO</span>
                    </div>
                </button>
                @endif

                <!-- Mercados en Vivo -->
                <button @click="activeTab = 'markets'; sessionStorage.setItem('portfolio_active_tab', 'markets')" 
                        :class="activeTab === 'markets' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/20 font-extrabold' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-900/50 font-bold'"
                        class="px-5 py-3 rounded-xl text-xs uppercase tracking-wider transition-all duration-200 flex items-center gap-3 shrink-0 cursor-pointer focus:outline-none">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor" class="w-6 h-6 shrink-0" style="width: 24px; height: 24px;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                    </svg>
                    <div class="flex flex-col items-start text-left leading-tight shrink-0">
                        <span class="text-[9px] font-bold tracking-wider opacity-85">MERCADOS</span>
                        <span class="text-xs font-black tracking-wide">EN VIVO</span>
                    </div>
                </button>
                
                @if(!$isPaper)
                <!-- Ingresar Fondos -->
                <button @click="activeTab = 'deposit_funds'; sessionStorage.setItem('portfolio_active_tab', 'deposit_funds')" 
                        :class="activeTab === 'deposit_funds' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/20 font-extrabold' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-900/50 font-bold'"
                        class="px-5 py-3 rounded-xl text-xs uppercase tracking-wider transition-all duration-200 flex items-center gap-3 shrink-0 cursor-pointer focus:outline-none">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor" class="w-6 h-6 shrink-0" style="width: 24px; height: 24px;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.33l-7.5-5-7.5 5V21m-2.25 0h24" />
                    </svg>
                    <div class="flex flex-col items-start text-left leading-tight shrink-0">
                        <span class="text-[9px] font-bold tracking-wider opacity-85">INGRESAR</span>
                        <span class="text-xs font-black tracking-wide">FONDOS</span>
                    </div>
                </button>
                @endif
            </div>

            <!-- TAB 0: PORTFOLIO VALUE (VALOR DE MI CARTERA) -->
            <div x-show="activeTab === 'portfolio_value'" class="space-y-6" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                <!-- Win/Loss Beautiful Banner -->
                <div class="mb-2">
                    @if($hasPositions)
                        @if($isWinning)
                            <div class="glass-panel rounded-2xl p-5 bg-gradient-to-r from-emerald-950/20 via-slate-900 to-indigo-950/20 border-emerald-500/20 shadow-lg relative overflow-hidden">
                                <div class="absolute right-0 top-0 w-48 h-48 bg-emerald-500/5 rounded-full blur-3xl pointer-events-none"></div>
                                <div class="flex items-start gap-4">
                                    <div class="p-3 bg-emerald-500/10 rounded-xl border border-emerald-500/20 text-emerald-400 shrink-0 shadow-inner">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-6 h-6">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 14.102-14.102M3.75 18h16.5" />
                                        </svg>
                                    </div>
                                    <div class="space-y-1">
                                        <div class="flex items-center gap-2">
                                            <span class="relative flex h-2 w-2">
                                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                                <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                                            </span>
                                            <h3 class="text-sm font-extrabold text-white tracking-tight uppercase">¡Tu portafolio está en verde! 🚀</h3>
                                        </div>
                                        <p class="text-xs text-slate-350 leading-relaxed font-medium">
                                            Actualmente estás ganando un total acumulado de <strong class="text-emerald-400 font-bold">${{ number_format($totalUnrealizedPL, 2) }}</strong> en tus posiciones abiertas. El bot de trading automático está gestionando tu capital con éxito.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="glass-panel rounded-2xl p-5 bg-gradient-to-r from-rose-950/20 via-slate-900 to-indigo-950/20 border-rose-500/20 shadow-lg relative overflow-hidden">
                                <div class="absolute right-0 top-0 w-48 h-48 bg-rose-500/5 rounded-full blur-3xl pointer-events-none"></div>
                                <div class="flex items-start gap-4">
                                    <div class="p-3 bg-rose-500/10 rounded-xl border border-rose-500/20 text-rose-400 shrink-0 shadow-inner">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-6 h-6">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6 9 12.75l4.306-4.306A11.95 11.95 0 0 1 15 21.75M3.75 6h16.5" />
                                        </svg>
                                    </div>
                                    <div class="space-y-1">
                                        <div class="flex items-center gap-2">
                                            <span class="relative flex h-2 w-2">
                                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                                                <span class="relative inline-flex rounded-full h-2 w-2 bg-rose-500"></span>
                                            </span>
                                            <h3 class="text-sm font-extrabold text-white tracking-tight uppercase">Tu portafolio registra un ajuste temporal 📉</h3>
                                        </div>
                                        <p class="text-xs text-slate-350 leading-relaxed font-medium">
                                            Tus posiciones actuales reflejan una variación de <strong class="text-rose-400 font-bold">${{ number_format($totalUnrealizedPL, 2) }}</strong>. La estrategia opera a mediano plazo y tus límites de pérdidas están activos para resguardar tu inversión.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @else
                        <div class="glass-panel rounded-2xl p-5 bg-gradient-to-r from-blue-950/20 via-slate-900 to-indigo-950/20 border-blue-500/20 shadow-lg relative overflow-hidden">
                            <div class="absolute right-0 top-0 w-48 h-48 bg-blue-500/5 rounded-full blur-3xl pointer-events-none"></div>
                            <div class="flex items-start gap-4">
                                <div class="p-3 bg-blue-500/10 rounded-xl border border-blue-500/20 text-blue-400 shrink-0 shadow-inner">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-6 h-6">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v5.25c0 .621-.504 1.125-1.125 1.125h-2.25A1.125 1.125 0 0 1 3 18.375v-5.25ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125v-9.75ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v14.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                                    </svg>
                                </div>
                                <div class="space-y-1">
                                    <div class="flex items-center gap-2">
                                        <span class="relative flex h-2 w-2">
                                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                                            <span class="relative inline-flex rounded-full h-2 w-2 bg-blue-500"></span>
                                        </span>
                                        <h3 class="text-sm font-extrabold text-white tracking-tight uppercase">Sin posiciones activas en cartera 📊</h3>
                                    </div>
                                    <p class="text-xs text-slate-350 leading-relaxed font-medium">
                                        Tu saldo líquido está disponible. Puedes configurar tu estrategia de trading automático en tu perfil y presionar "Ejecutar Bot" para iniciar operaciones.
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- 6 Cards Summary Block -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-5">
                    <!-- Valor de Cartera -->
                    <div class="glass-panel rounded-2xl p-5 shadow-xl space-y-2 relative bg-gradient-to-tr from-slate-900 to-indigo-950/30 group">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] text-slate-500 font-bold uppercase tracking-wider block">Valor de Cartera</span>
                            <!-- Tooltip -->
                            <div class="relative" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false" @click.away="open = false">
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
                                     class="absolute bottom-full right-0 mb-2.5 z-50">
                                     <div class="relative bg-slate-950/95 backdrop-blur-md text-slate-350 text-[10px] p-2.5 rounded-xl border border-slate-800/80 shadow-2xl leading-normal font-medium">
                                        Es la suma de tu dinero en efectivo más el valor actual de tus acciones.
                                        <div class="absolute top-full right-1.5 -mt-[5px] w-2 h-2 bg-slate-950 border-r border-b border-slate-800/80 transform rotate-45"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <span class="text-2xl font-extrabold text-white block">${{ number_format($portfolioValue, 2) }}</span>
                        <span class="text-[10px] text-slate-500 font-medium block">Efectivo + Acciones</span>
                    </div>

                    <!-- Capital Invertido / Dinero Invertido -->
                    <div class="glass-panel rounded-2xl p-5 shadow-xl space-y-2 relative group">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] text-slate-500 font-bold uppercase tracking-wider block">Dinero Invertido</span>
                            <!-- Tooltip -->
                            <div class="relative" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false" @click.away="open = false">
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
                                     class="absolute bottom-full right-0 mb-2.5 z-50">
                                     <div class="relative bg-slate-950/95 backdrop-blur-md text-slate-350 text-[10px] p-2.5 rounded-xl border border-slate-800/80 shadow-2xl leading-normal font-medium">
                                        Es el costo de adquisición de todas las acciones/unidades que tienes compradas.
                                        <div class="absolute top-full right-1.5 -mt-[5px] w-2 h-2 bg-slate-950 border-r border-b border-slate-800/80 transform rotate-45"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <span class="text-2xl font-extrabold text-slate-200 block">${{ number_format($totalCostBasis, 2) }}</span>
                        <span class="text-[10px] text-slate-500 font-medium block">Costo de adquisición</span>
                    </div>

                    <!-- Ganancia/Pérdida Total -->
                    <div class="glass-panel rounded-2xl p-5 shadow-xl space-y-2 relative bg-gradient-to-tr {{ $isWinning ? 'from-slate-900 to-emerald-950/25 border-emerald-500/10' : 'from-slate-900 to-rose-950/25 border-rose-500/10' }} group">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] text-slate-500 font-bold uppercase tracking-wider block">Ganancia / Pérdida</span>
                            <!-- Tooltip -->
                            <div class="relative" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false" @click.away="open = false">
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
                                     class="absolute bottom-full right-0 mb-2.5 z-50">
                                     <div class="relative bg-slate-950/95 backdrop-blur-md text-slate-350 text-[10px] p-2.5 rounded-xl border border-slate-800/80 shadow-2xl leading-normal font-medium">
                                        Muestra tu ganancia o pérdida no realizada tanto en dólares como en porcentaje.
                                        <div class="absolute top-full right-1.5 -mt-[5px] w-2 h-2 bg-slate-950 border-r border-b border-slate-800/80 transform rotate-45"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <span class="text-2xl font-extrabold {{ $isWinning ? 'text-emerald-400' : 'text-rose-400' }} block">
                            {{ $isWinning ? '+' : '' }}${{ number_format($totalUnrealizedPL, 2) }}
                        </span>
                        <span class="text-[10px] {{ $isWinning ? 'text-emerald-500' : 'text-rose-500' }} font-bold block">
                            {{ $isWinning ? '+' : '' }}{{ number_format($totalPLPercent, 2) }}%
                        </span>
                    </div>

                    <!-- Cash & Capital (Efectivo) -->
                    <div class="glass-panel rounded-2xl p-5 shadow-xl space-y-2 relative group">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] text-slate-500 font-bold uppercase tracking-wider block">Efectivo (Cash)</span>
                            <!-- Tooltip -->
                            <div class="relative" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false" @click.away="open = false">
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
                                     class="absolute bottom-full right-0 mb-2.5 z-50">
                                     <div class="relative bg-slate-950/95 backdrop-blur-md text-slate-350 text-[10px] p-2.5 rounded-xl border border-slate-800/80 shadow-2xl leading-normal font-medium">
                                        Es el saldo líquido en tu cuenta que no está invertido.
                                        <div class="absolute top-full right-1.5 -mt-[5px] w-2 h-2 bg-slate-950 border-r border-b border-slate-800/80 transform rotate-45"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <span class="text-2xl font-extrabold text-slate-200 block">${{ number_format($cash, 2) }}</span>
                        <span class="text-[10px] text-slate-500 font-medium block">Saldo libre líquido</span>
                    </div>

                    <!-- Buying Power -->
                    <div class="glass-panel rounded-2xl p-5 shadow-xl space-y-2 relative group">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] text-slate-500 font-bold uppercase tracking-wider block">Poder de Compra</span>
                            <!-- Tooltip -->
                            <div class="relative" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false" @click.away="open = false">
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
                                     class="absolute bottom-full right-0 mb-2.5 z-50">
                                     <div class="relative bg-slate-950/95 backdrop-blur-md text-slate-350 text-[10px] p-2.5 rounded-xl border border-slate-800/80 shadow-2xl leading-normal font-medium">
                                        Límite máximo de capital que puedes emplear para comprar activos.
                                        <div class="absolute top-full right-1.5 -mt-[5px] w-2 h-2 bg-slate-950 border-r border-b border-slate-800/80 transform rotate-45"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <span class="text-2xl font-extrabold text-indigo-400 block">${{ number_format($buyingPower, 2) }}</span>
                        <span class="text-[10px] text-slate-500 font-medium block">Apalancamiento incl.</span>
                    </div>

                    <!-- Estado del Bot (Último Run) -->
                    <div class="glass-panel rounded-2xl p-5 shadow-xl space-y-2 relative group">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] text-slate-500 font-bold uppercase tracking-wider block">Estado del Bot</span>
                            <!-- Tooltip -->
                            <div class="relative" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false" @click.away="open = false">
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
                                     class="absolute bottom-full right-0 mb-2.5 z-50">
                                     <div class="relative bg-slate-950/95 backdrop-blur-md text-slate-350 text-[10px] p-2.5 rounded-xl border border-slate-800/80 shadow-2xl leading-normal font-medium">
                                        Muestra la última ejecución registrada del bot de trading automático.
                                        <div class="absolute top-full right-1.5 -mt-[5px] w-2 h-2 bg-slate-950 border-r border-b border-slate-800/80 transform rotate-45"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @if($lastExecution)
                            <span class="text-2xl font-extrabold uppercase {{ $lastExecution->status === 'success' ? 'text-green-400' : 'text-red-400' }} block">
                                {{ $lastExecution->status === 'success' ? 'OK' : 'Fallo' }}
                            </span>
                            <span class="text-[10px] text-slate-500 font-medium block">Hace {{ $lastExecution->started_at->timezone('Europe/Madrid')->diffForHumans() }}</span>
                        @else
                            <span class="text-2xl font-extrabold text-slate-500 block">N/A</span>
                            <span class="text-[10px] text-slate-500 font-medium block">Nunca ejecutado</span>
                        @endif
                    </div>
                </div>

                <!-- Limits Progress Bars inside Portfolio Value Tab -->
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
                            <span>Límite diario establecido para evitar pérdidas excesivas en un solo día.</span>
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
                            <span>Límite semanal establecido para controlar el presupuesto de compra acumulado.</span>
                            <span class="font-bold text-slate-400">{{ round($weeklyPercent, 1) }}%</span>
                        </div>
                    </div>
                </div>
            </div>



            <!-- TAB 2: POSITIONS -->
            <div x-show="activeTab === 'positions'" class="space-y-6" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" style="display: none;">
                <!-- Open Positions Table -->
                <div class="space-y-4">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-2">
                        <div class="space-y-1">
                            <h2 class="text-lg font-extrabold text-white flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-indigo-400">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 0 0 6 16.5h2.25M3.75 3h-1.5m1.5 0v11.25m0 0h19.5m0 0h-2.25m-14.25-2.25h14.25m-14.25-3h14.25m-14.25-3H21" />
                                </svg>
                                Mis Acciones Actuales
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
                                                <span class="text-slate-500">Activo</span>
                                                <div class="relative inline-block" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false" @click.away="open = false">
                                                    <svg @click="open = !open" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3.5 h-3.5 text-slate-400 hover:text-indigo-400 transition-colors cursor-pointer">
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
                                                         class="absolute top-full left-0 mt-2 z-50 text-left normal-case">
                                                        <div class="relative bg-slate-950/95 backdrop-blur-md text-slate-350 text-[10px] p-2.5 rounded-xl border border-slate-800/80 shadow-2xl leading-normal font-medium">
                                                            Código abreviado del activo en la bolsa de valores (ej: AAPL para Apple).
                                                            <!-- Caret pointing UP -->
                                                            <div class="absolute bottom-full left-1.5 -mb-[5px] w-2 h-2 bg-slate-950 border-t border-l border-slate-800/80 transform rotate-45"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </th>
                                        <th class="py-4 px-5 text-right text-slate-500 font-bold uppercase tracking-wider">
                                            Fecha Compra
                                        </th>
                                        <th class="py-4 px-5 text-right">
                                            <div class="flex items-center justify-end gap-1.5">
                                                <span class="text-slate-500">Cantidad</span>
                                                <div class="relative inline-block" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false" @click.away="open = false">
                                                    <svg @click="open = !open" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3.5 h-3.5 text-slate-400 hover:text-indigo-400 transition-colors cursor-pointer">
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
                                                         class="absolute top-full right-0 mt-2 z-50 text-left normal-case">
                                                        <div class="relative bg-slate-950/95 backdrop-blur-md text-slate-350 text-[10px] p-2.5 rounded-xl border border-slate-800/80 shadow-2xl leading-normal font-medium">
                                                            Número total de unidades o acciones de este activo que posees actualmente.
                                                            <!-- Caret pointing UP -->
                                                            <div class="absolute bottom-full right-1.5 -mb-[5px] w-2 h-2 bg-slate-950 border-t border-l border-slate-800/80 transform rotate-45"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </th>
                                        <th class="py-4 px-5 text-right">
                                            <div class="flex items-center justify-end gap-1.5">
                                                <span class="text-slate-500">Precio Actual</span>
                                                <div class="relative inline-block" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false" @click.away="open = false">
                                                    <svg @click="open = !open" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3.5 h-3.5 text-slate-400 hover:text-indigo-400 transition-colors cursor-pointer">
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
                                                         class="absolute top-full right-0 mt-2 z-50 text-left normal-case">
                                                        <div class="relative bg-slate-950/95 backdrop-blur-md text-slate-350 text-[10px] p-2.5 rounded-xl border border-slate-800/80 shadow-2xl leading-normal font-medium">
                                                            Precio de cotización del activo hoy en el mercado en tiempo real.
                                                            <!-- Caret pointing UP -->
                                                            <div class="absolute bottom-full right-1.5 -mb-[5px] w-2 h-2 bg-slate-950 border-t border-l border-slate-800/80 transform rotate-45"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </th>
                                        <th class="py-4 px-5 text-right bg-indigo-500/5">
                                            <div class="flex items-center justify-end gap-1.5">
                                                <span class="text-indigo-300 font-extrabold">Dinero Invertido</span>
                                                <div class="relative inline-block" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false" @click.away="open = false">
                                                    <svg @click="open = !open" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3.5 h-3.5 text-indigo-400 hover:text-indigo-350 transition-colors cursor-pointer">
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
                                                         class="absolute top-full right-0 mt-2 z-50 text-left normal-case">
                                                        <div class="relative bg-slate-950/95 backdrop-blur-md text-slate-350 text-[10px] p-2.5 rounded-xl border border-slate-800/80 shadow-2xl leading-normal font-medium">
                                                            Dinero total invertido en comprar estas acciones (Cantidad × Precio de Compra).
                                                            <!-- Caret pointing UP -->
                                                            <div class="absolute bottom-full right-1.5 -mb-[5px] w-2 h-2 bg-slate-950 border-t border-l border-slate-800/80 transform rotate-45"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </th>
                                        <th class="py-4 px-5 text-right bg-emerald-500/5">
                                            <div class="flex items-center justify-end gap-1.5">
                                                <span class="text-emerald-300 font-extrabold">Dinero al Vender (en el acto)</span>
                                                <div class="relative inline-block" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false" @click.away="open = false">
                                                    <svg @click="open = !open" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3.5 h-3.5 text-emerald-400 hover:text-emerald-300 transition-colors cursor-pointer">
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
                                                         class="absolute top-full right-0 mt-2 z-50 text-left normal-case">
                                                        <div class="relative bg-slate-950/95 backdrop-blur-md text-slate-350 text-[10px] p-2.5 rounded-xl border border-slate-800/80 shadow-2xl leading-normal font-medium">
                                                            Dinero que recibirás en tu balance si vendes esta posición ahora mismo (Cantidad × Precio Actual).
                                                            <!-- Caret pointing UP -->
                                                            <div class="absolute bottom-full right-1.5 -mb-[5px] w-2 h-2 bg-slate-950 border-t border-l border-slate-800/80 transform rotate-45"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </th>
                                        <th class="py-4 px-5 text-right">
                                            <div class="flex items-center justify-end gap-1.5">
                                                <span class="text-slate-500">Resultado si Vendes Ahora</span>
                                                <div class="relative inline-block" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false" @click.away="open = false">
                                                    <svg @click="open = !open" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3.5 h-3.5 text-slate-400 hover:text-indigo-400 transition-colors cursor-pointer">
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
                                                         class="absolute top-full right-0 mt-2 z-50 text-left normal-case">
                                                        <div class="relative bg-slate-950/95 backdrop-blur-md text-slate-350 text-[10px] p-2.5 rounded-xl border border-slate-800/80 shadow-2xl leading-normal font-medium">
                                                            El dinero neto exacto que ganas o pierdes respecto a tu inversión inicial si decides vender en este instante.
                                                            <!-- Caret pointing UP -->
                                                            <div class="absolute bottom-full right-1.5 -mb-[5px] w-2 h-2 bg-slate-950 border-t border-l border-slate-800/80 transform rotate-45"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </th>
                                        <th class="py-4 px-5 text-center text-slate-500">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-900/50">
                                    @if(empty($positions))
                                        <tr>
                                             <td colspan="8" class="py-8 px-5 text-center">
                                                 <div class="text-sm text-slate-400 mb-2">No tienes posiciones abiertas en este momento.</div>
                                                 <div class="text-[11px] text-slate-500 max-w-2xl mx-auto bg-slate-950/40 border border-slate-900 p-3.5 rounded-xl leading-relaxed">
                                                     💡 <strong>¿Por qué no se han comprado acciones en modo Real?</strong> El bot automático analiza los activos y compra únicamente si experimentan una caída diaria superior al umbral configurado de <strong class="text-indigo-400">{{ ($isPaper ? Auth::user()->bot_buy_threshold : Auth::user()->live_bot_buy_threshold) ?? -1.5 }}%</strong>. Como ningún activo ha caído por debajo de ese límite, y para proteger tu dinero, no se han emitido órdenes. Puedes cambiar tus parámetros de compra o el nivel de riesgo sugerido en las opciones de configuración.
                                                 </div>
                                             </td>
                                        </tr>
                                    @else
                                        @foreach($positions as $pos)
                                            @php
                                                $isPositive = $pos['unrealized_pl'] >= 0;
                                                $colorClass = $isPositive ? 'text-green-400' : 'text-red-400';
                                                $bgColorClass = $isPositive ? 'bg-green-500/10' : 'bg-red-500/10';
                                                
                                                $venderColorClass = $isPositive ? 'text-emerald-400' : 'text-rose-400';
                                                $venderBgClass = $isPositive ? 'bg-emerald-500/5' : 'bg-rose-500/5';
                                            @endphp
                                            <tr class="hover:bg-slate-950/40 transition duration-150 group cursor-pointer" onclick="window.location.href='{{ route('assets.show', $pos['symbol']) }}'">
                                                <td class="py-4.5 px-5">
                                                    <div class="flex flex-col">
                                                        @php
                                                            $friendlyName = $friendlyNames[$pos['symbol']] ?? $pos['name'] ?? $pos['symbol'];
                                                        @endphp
                                                        <span class="font-extrabold text-sm text-white group-hover:text-indigo-400 transition">{{ $friendlyName }}</span>
                                                        <div class="flex items-center gap-1.5 mt-0.5">
                                                            <span class="text-[10px] text-slate-500 font-medium">{{ $pos['symbol'] }}</span>
                                                            @if(isset($pos['pending_qty']) && $pos['pending_qty'] > 0)
                                                                @if($pos['available_qty'] <= 0)
                                                                    <span class="inline-flex items-center text-[9px] font-extrabold px-1.5 py-0.2 rounded bg-amber-500/10 text-amber-400 border border-amber-500/25 animate-pulse">
                                                                        VENTA EN COLA
                                                                    </span>
                                                                @else
                                                                    <span class="inline-flex items-center text-[9px] font-extrabold px-1.5 py-0.2 rounded bg-emerald-500/10 text-emerald-400 border border-emerald-500/25">
                                                                        COMPRADO
                                                                    </span>
                                                                    <span class="inline-flex items-center text-[9px] font-bold px-1.5 py-0.2 rounded bg-amber-500/10 text-amber-400 border border-amber-500/20">
                                                                        {{ number_format($pos['pending_qty'], 2) }} EN COLA
                                                                    </span>
                                                                @endif
                                                            @else
                                                                <span class="inline-flex items-center text-[9px] font-extrabold px-1.5 py-0.2 rounded bg-emerald-500/10 text-emerald-400 border border-emerald-500/25">
                                                                    COMPRADO
                                                                </span>
                                                            @endif
                                                            @if(isset($pos['highest_price']))
                                                                @php
                                                                    $takeProfitPercent = ($isPaper ? Auth::user()->bot_take_profit : Auth::user()->live_bot_take_profit) ?? 2.0;
                                                                    $profitThreshold = $pos['avg_entry_price'] * (1 + $takeProfitPercent / 100);
                                                                @endphp
                                                                @if($pos['highest_price'] >= $profitThreshold)
                                                                    <span class="inline-flex items-center text-[9px] font-extrabold px-1.5 py-0.2 rounded bg-fuchsia-500/10 text-fuchsia-400 border border-fuchsia-500/25 animate-pulse">
                                                                        TRAILING ACTIVO
                                                                    </span>
                                                                @endif
                                                            @endif
                                                            @if(isset($pos['dca_level']) && $pos['dca_level'] > 0)
                                                                <span class="inline-flex items-center text-[9px] font-extrabold px-1.5 py-0.2 rounded bg-cyan-500/10 text-cyan-400 border border-cyan-500/25">
                                                                    DCA: {{ $pos['dca_level'] }}/3
                                                                </span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="py-4.5 px-5 text-right font-medium text-slate-400 text-xs">
                                                    @if(isset($pos['purchase_date']) && $pos['purchase_date'])
                                                        <div class="font-semibold text-slate-350">
                                                            {{ $pos['purchase_date']->timezone('Europe/Madrid')->format('d/m/Y') }}
                                                        </div>
                                                        <div class="text-[10px] text-slate-500 mt-0.5">
                                                            {{ $pos['purchase_date']->timezone('Europe/Madrid')->format('H:i') }}
                                                        </div>
                                                    @else
                                                        <span class="text-slate-600 font-medium">-</span>
                                                    @endif
                                                </td>
                                                <td class="py-4.5 px-5 text-right font-semibold text-slate-200 text-sm">
                                                    <div class="flex flex-col items-end">
                                                        <span class="font-mono text-slate-200">{{ number_format($pos['qty'], 4) }}</span>
                                                        @if(isset($pos['pending_qty']) && $pos['pending_qty'] > 0)
                                                            <span class="text-[9px] text-amber-400 font-semibold mt-0.5">
                                                                {{ number_format($pos['available_qty'], 4) }} disp.
                                                            </span>
                                                        @else
                                                            <span class="text-[9px] text-slate-500 font-bold uppercase mt-0.5">
                                                                {{ (str_contains($pos['symbol'], 'BTC') || str_contains($pos['symbol'], 'ETH') || str_contains($pos['symbol'], 'USD') || str_contains($pos['symbol'], '/')) ? 'unidades' : 'acciones' }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td class="py-4.5 px-5 text-right font-bold text-slate-200 text-sm font-mono">
                                                    ${{ number_format($pos['current_price'], 2) }}
                                                </td>
                                                <td class="py-4.5 px-5 text-right font-bold text-indigo-300 text-sm font-mono bg-indigo-500/5">
                                                    ${{ number_format($pos['cost_basis'], 2) }}
                                                </td>
                                                <td class="py-4.5 px-5 text-right font-black {{ $venderColorClass }} text-sm font-mono {{ $venderBgClass }}">
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
                                                <td class="py-4.5 px-5 text-center">
                                                    @if(isset($pos['available_qty']) && $pos['available_qty'] <= 0)
                                                        <button disabled onclick="event.stopPropagation();" class="px-3 py-1.5 text-xs font-bold text-slate-500 bg-slate-800/20 border border-slate-800/40 rounded-lg cursor-not-allowed">
                                                            En Cola
                                                        </button>
                                                    @else
                                                        <form action="{{ route('trade.execute') }}" method="POST" class="inline" onsubmit="event.stopPropagation(); return confirm('¿Estás seguro de que deseas vender las {{ $pos['available_qty'] ?? $pos['qty'] }} acciones de {{ $pos['symbol'] }} a precio de mercado?');" onclick="event.stopPropagation();">
                                                            @csrf
                                                            <input type="hidden" name="active_tab" value="positions">
                                                            <input type="hidden" name="symbol" value="{{ $pos['symbol'] }}">
                                                            <input type="hidden" name="qty" value="{{ $pos['available_qty'] ?? $pos['qty'] }}">
                                                            <input type="hidden" name="side" value="sell">
                                                            <input type="hidden" name="type" value="market">
                                                            <button type="submit" onclick="event.stopPropagation();" class="px-3 py-1.5 text-xs font-bold text-red-400 hover:text-white bg-red-500/10 hover:bg-red-500 border border-red-500/30 rounded-lg transition duration-150 shadow-sm shadow-red-500/5 hover:shadow-red-500/20 cursor-pointer">
                                                                Vender
                                                             </button>
                                                        </form>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach

                                        <!-- Total Row -->
                                        <tr class="bg-slate-950/65 font-bold border-t-2 border-indigo-500/25">
                                            <td class="py-4.5 px-5 text-indigo-400 font-extrabold text-xs uppercase tracking-wider">
                                                Total Cartera
                                            </td>
                                            <td class="py-4.5 px-5 text-right text-slate-500 text-xs font-normal">
                                                -
                                            </td>
                                            <td class="py-4.5 px-5 text-right text-slate-400 text-xs font-bold font-mono">
                                                {{ count($positions) }} ACTIVOS
                                            </td>
                                            <td class="py-4.5 px-5 text-right text-slate-500 text-xs font-normal">
                                                -
                                            </td>
                                            <td class="py-4.5 px-5 text-right text-slate-300 text-sm font-extrabold font-mono bg-indigo-500/5">
                                                ${{ number_format($totalCostBasis, 2) }}
                                            </td>
                                            @php
                                                $totalIsPositive = $totalUnrealizedPL >= 0;
                                                $totalVenderColor = $totalIsPositive ? 'text-emerald-400' : 'text-rose-400';
                                                $totalVenderBg = $totalIsPositive ? 'bg-emerald-500/5' : 'bg-rose-500/5';
                                            @endphp
                                            <td class="py-4.5 px-5 text-right {{ $totalVenderColor }} text-sm font-extrabold font-mono {{ $totalVenderBg }}">
                                                ${{ number_format($totalMarketValue, 2) }}
                                            </td>
                                            <td class="py-4.5 px-5 text-right">
                                                @php
                                                    $totalPLColor = $totalUnrealizedPL >= 0 ? 'text-green-400' : 'text-red-400';
                                                    $totalPLBg = $totalUnrealizedPL >= 0 ? 'bg-green-500/10' : 'bg-red-500/10';
                                                @endphp
                                                <div class="flex flex-col items-end">
                                                    <span class="text-sm font-extrabold {{ $totalPLColor }} font-mono">
                                                        {{ $totalUnrealizedPL >= 0 ? '+' : '' }}${{ number_format($totalUnrealizedPL, 2) }}
                                                    </span>
                                                    <span class="inline-block px-1.5 py-0.5 rounded text-[10px] font-bold {{ $totalPLColor }} {{ $totalPLBg }} mt-0.5">
                                                        {{ $totalUnrealizedPL >= 0 ? '+' : '' }}{{ number_format($totalPLPercent, 2) }}%
                                                    </span>
                                                </div>
                                            </td>
                                            <td class="py-4.5 px-5 text-center">
                                                <!-- Actions empty -->
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Recent Trades History -->
                <div class="space-y-4 border-t border-slate-900/60 pt-8 mt-4">
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
                                        <th class="py-4 px-5">Activo</th>
                                        <th class="py-4 px-5 text-right">Cantidad</th>
                                        <th class="py-4 px-5 text-right">Precio Ejecución</th>
                                        <th class="py-4 px-5 text-right">Total</th>
                                        <th class="py-4 px-5 text-right">Resultado (G/P)</th>
                                        <th class="py-4 px-5 text-center">Estado</th>
                                        <th class="py-4 px-5 text-right">Modo</th>
                                        <th class="py-4 px-5 text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-900/50">
                                    @if($recentTrades->isEmpty())
                                        <tr>
                                            <td colspan="11" class="py-8 text-center text-sm text-slate-500">Aún no se han registrado transacciones en esta cuenta.</td>
                                        </tr>
                                    @else
                                        @foreach($recentTrades as $trade)
                                            @php
                                                $isBuy = $trade->side === 'buy';
                                                $tradeTotal = $trade->qty * $trade->price;
                                            @endphp
                                            <tr class="hover:bg-slate-950/20 transition">
                                                <td class="py-3.5 px-5 text-xs text-slate-400 font-medium">
                                                    @if($isBuy)
                                                        <div class="font-semibold text-slate-300">
                                                            {{ $trade->created_at->timezone('Europe/Madrid')->format('d/m/Y, H:i') }}
                                                        </div>
                                                        <div class="text-[9px] text-slate-500">Fecha de Compra</div>
                                                    @else
                                                        <div class="font-semibold text-slate-300">
                                                            {{ $trade->created_at->timezone('Europe/Madrid')->format('d/m/Y, H:i') }}
                                                        </div>
                                                        @php
                                                            $purchaseTrade = \App\Models\Trade::where('user_id', $trade->user_id)
                                                                ->where('symbol', $trade->symbol)
                                                                ->where('side', 'buy')
                                                                ->where('status', 'filled')
                                                                ->where('is_dry_run', $trade->is_dry_run)
                                                                ->where('created_at', '<', $trade->created_at)
                                                                ->latest()
                                                                ->first();
                                                        @endphp
                                                        <div class="text-[9px] text-slate-500 mt-0.5">
                                                            @if($purchaseTrade)
                                                                Compra: {{ $purchaseTrade->created_at->timezone('Europe/Madrid')->format('d/m/Y, H:i') }}
                                                            @else
                                                                Compra: N/D
                                                            @endif
                                                        </div>
                                                    @endif
                                                </td>
                                                <td class="py-3.5 px-5">
                                                    <span class="text-[10px] px-2 py-0.5 rounded font-extrabold uppercase {{ $isBuy ? 'bg-green-500/10 text-green-400' : 'bg-red-500/10 text-red-400' }}">
                                                        {{ $isBuy ? 'Compra' : 'Venta' }}
                                                    </span>
                                                </td>
                                                <td class="py-3.5 px-5 text-xs text-slate-400 font-bold">
                                                    {{ $trade->bot_execution_id ? 'Bot Automático' : 'Manual' }}
                                                </td>
                                                <td class="py-3.5 px-5">
                                                    <div class="flex flex-col">
                                                        @php
                                                            $friendlyName = $friendlyNames[$trade->symbol] ?? $trade->symbol;
                                                        @endphp
                                                        <span class="font-extrabold text-sm text-white">{{ $friendlyName }}</span>
                                                        <span class="text-[9px] text-slate-500 font-medium">{{ $trade->symbol }}</span>
                                                    </div>
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
                                                <td class="py-3.5 px-5 text-right text-xs font-bold">
                                                    @if(!$isBuy && isset($trade->pnl))
                                                        @php
                                                            $pnl = (float)$trade->pnl;
                                                            $pnlColorClass = $pnl >= 0 ? 'text-green-400' : 'text-red-400';
                                                            $pnlSign = $pnl >= 0 ? '+' : '';
                                                            $costBasis = ($tradeTotal - $pnl);
                                                            $returnPercent = $costBasis > 0 ? ($pnl / $costBasis) * 100 : 0;
                                                        @endphp
                                                        <span class="{{ $pnlColorClass }}">
                                                            {{ $pnlSign }}${{ number_format($pnl, 2) }}
                                                            <span class="text-[9px] font-medium opacity-80">({{ $pnlSign }}{{ number_format($returnPercent, 1) }}%)</span>
                                                        </span>
                                                    @else
                                                        <span class="text-slate-500 font-medium">-</span>
                                                    @endif
                                                </td>
                                                <td class="py-3.5 px-5 text-center">
                                                    @php
                                                        $status = strtolower($trade->status ?? 'filled');
                                                        $isCompleted = $status === 'filled';
                                                        $statusText = $isCompleted ? 'Completada' : ($status === 'rejected' ? 'Rechazada' : ($status === 'canceled' ? 'Cancelada' : 'En Cola'));
                                                        $statusColorClass = $isCompleted ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : ($status === 'rejected' || $status === 'canceled' ? 'bg-rose-500/10 text-rose-400 border border-rose-500/20' : 'bg-amber-500/10 text-amber-400 border border-amber-500/20');
                                                    @endphp
                                                    <span class="text-[9px] px-2 py-0.5 rounded font-bold uppercase {{ $statusColorClass }}">
                                                        {{ $statusText }}
                                                    </span>
                                                </td>
                                                <td class="py-3.5 px-5 text-right">
                                                    <span class="text-[10px] px-1.5 py-0.5 rounded font-medium uppercase {{ $trade->is_dry_run ? 'bg-amber-500/10 text-amber-400' : 'bg-green-500/10 text-green-400' }}">
                                                        {{ $trade->is_dry_run ? 'Simulado' : 'Real' }}
                                                    </span>
                                                </td>
                                                <td class="py-3.5 px-5 text-center">
                                                    @if(!in_array($status, ['filled', 'rejected', 'canceled', 'cancelled', 'expired']))
                                                        <form action="{{ route('trade.cancel', $trade->id) }}" method="POST" class="inline m-0" onsubmit="return confirm('¿Estás seguro de que deseas cancelar esta orden pendiente en el bróker?')">
                                                            @csrf
                                                            <button type="submit" class="px-2.5 py-1 bg-red-950/40 border border-red-500/30 text-red-400 hover:bg-red-500/20 rounded text-[10px] font-extrabold uppercase transition cursor-pointer">
                                                                Cancelar
                                                            </button>
                                                        </form>
                                                    @else
                                                        <span class="text-[10px] text-slate-600">-</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                @if(!$recentRealSells->isEmpty())
                    <div class="space-y-3 mt-8">
                        <h2 class="text-sm font-extrabold text-white uppercase tracking-wider flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 text-emerald-400">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1 3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z" />
                            </svg>
                            Últimos Cierres y Retornos (G/P Realizada)
                        </h2>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                            @foreach($recentRealSells as $rSell)
                                @php
                                    $rPnl = (float)$rSell->pnl;
                                    $rIsPositive = $rPnl >= 0;
                                    $rPnlColor = $rIsPositive ? 'text-emerald-400 bg-emerald-950/20 border-emerald-500/10' : 'text-rose-400 bg-rose-950/20 border-rose-500/10';
                                    $rTradeTotal = $rSell->qty * $rSell->price;
                                    $rCostBasis = $rTradeTotal - $rPnl;
                                    $rReturnPercent = $rCostBasis > 0 ? ($rPnl / $rCostBasis) * 100 : 0;
                                @endphp
                                <div class="p-3 bg-slate-950/50 rounded-xl border border-slate-900 flex flex-col justify-between gap-1.5 relative group/card">
                                    <div class="flex items-center justify-between">
                                        <span class="text-[9px] px-1.5 py-0.5 rounded font-extrabold uppercase bg-red-500/10 text-red-400 border border-red-500/15">
                                            VENTA (CIERRE)
                                        </span>
                                        <span class="text-[9px] text-slate-500 font-bold">{{ $rSell->created_at->timezone('Europe/Madrid')->format('d/m H:i') }}</span>
                                    </div>
                                    <div>
                                        <span class="font-extrabold text-sm text-white block group-hover/card:text-indigo-400 transition">{{ $friendlyNames[$rSell->symbol] ?? $rSell->symbol }}</span>
                                        <span class="text-[10px] text-slate-400 block mt-0.5">{{ $rSell->qty }} uds a ${{ number_format($rSell->price, 2) }}</span>
                                    </div>
                                    <div class="border-t border-slate-900/60 pt-2 mt-0.5 flex items-center justify-between">
                                        <span class="text-[9px] text-slate-500 font-semibold">Balance G/P:</span>
                                        <span class="text-xs font-mono font-extrabold px-1.5 py-0.5 rounded border {{ $rPnlColor }}">
                                            {{ $rIsPositive ? '+' : '' }}${{ number_format($rPnl, 2) }}
                                            <span class="text-[9px] opacity-85">({{ $rIsPositive ? '+' : '' }}{{ number_format($rReturnPercent, 1) }}%)</span>
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <!-- TAB 3: BOT -->
            @if(!$isPaper)
            <div x-show="activeTab === 'risk_level'" class="space-y-6" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" style="display: none;">
                @php
                    $isConservativeActive = (
                        abs((float)Auth::user()->live_bot_buy_threshold - (-3.0)) < 0.0001 &&
                        abs((float)Auth::user()->live_bot_take_profit - 1.2) < 0.0001 &&
                        abs((float)Auth::user()->live_bot_stop_loss - (-1.5)) < 0.0001 &&
                        abs((float)Auth::user()->live_bot_order_size - 100.0) < 0.0001 &&
                        abs((float)Auth::user()->live_daily_spend_limit - 200.0) < 0.0001
                    );

                    $isRiskyActive = (
                        abs((float)Auth::user()->live_bot_buy_threshold - (-0.5)) < 0.0001 &&
                        abs((float)Auth::user()->live_bot_take_profit - 10.0) < 0.0001 &&
                        abs((float)Auth::user()->live_bot_stop_loss - (-8.0)) < 0.0001 &&
                        abs((float)Auth::user()->live_bot_order_size - 1000.0) < 0.0001 &&
                        abs((float)Auth::user()->live_daily_spend_limit - 3000.0) < 0.0001
                    );

                    $isExtremeActive = (
                        abs((float)Auth::user()->live_bot_buy_threshold - (-0.2)) < 0.0001 &&
                        abs((float)Auth::user()->live_bot_take_profit - 20.0) < 0.0001 &&
                        abs((float)Auth::user()->live_bot_stop_loss - (-15.0)) < 0.0001 &&
                        abs((float)Auth::user()->live_bot_order_size - 2000.0) < 0.0001 &&
                        abs((float)Auth::user()->live_daily_spend_limit - 5000.0) < 0.0001
                    );

                    $isCustomActive = !$isConservativeActive && !$isRiskyActive && !$isExtremeActive;
                @endphp

                <div class="glass-panel rounded-2xl p-6 bg-slate-950/60 border border-slate-900/80 shadow-2xl space-y-6">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-900 pb-4">
                        <div class="space-y-1">
                            <h2 class="text-lg font-extrabold text-white flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-indigo-400">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                                </svg>
                                Nivel de Riesgo Sugerido (Operaciones Reales)
                            </h2>
                            <p class="text-xs text-slate-400">
                                Selecciona un nivel de riesgo sugerido o mantén tu configuración personalizada. Los límites se aplicarán instantáneamente sobre las operaciones del bot en vivo.
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
                        <!-- CONSERVADOR CARD -->
                        <div class="glass-panel rounded-2xl p-5 border flex flex-col justify-between transition-all duration-300 {{ $isConservativeActive ? 'bg-emerald-950/15 border-emerald-500/30 shadow-lg shadow-emerald-550/5 ring-1 ring-emerald-500/20' : 'bg-slate-950/40 border-slate-900/60 hover:bg-slate-950/60' }}">
                            <div class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold uppercase tracking-wider text-emerald-400 flex items-center gap-1.5">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4">
                                            <path fill-rule="evenodd" d="M12.516 2.17a.75.75 0 0 0-1.032 0 11.209 11.209 0 0 1-7.877 3.08.75.75 0 0 0-.722.515A12.74 12.74 0 0 0 2.25 9.75c0 5.942 4.064 10.933 9.563 12.358a.75.75 0 0 0 .374 0c5.499-1.425 9.563-6.416 9.563-12.358 0-1.381-.22-2.71-.625-3.955a.75.75 0 0 0-.722-.515 11.21 11.21 0 0 1-7.877-3.08Z" clip-rule="evenodd" />
                                        </svg>
                                        Conservador
                                    </span>
                                    @if($isConservativeActive)
                                        <span class="text-[9px] font-black uppercase tracking-wider px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">ACTIVO</span>
                                    @endif
                                </div>
                                <p class="text-xs text-slate-300 leading-relaxed font-medium">
                                    Modera los límites de tu perfil de usuario para que tanto las ganancias como las pérdidas potenciales sean mínimas, asegurando un comportamiento de inversión muy defensivo.
                                </p>
                                <div class="bg-black/40 rounded-xl p-3.5 border border-slate-900/60 space-y-2">
                                    <div class="flex justify-between text-xs font-mono">
                                        <span class="text-slate-500">Caída de Compra:</span>
                                        <span class="text-slate-300 font-extrabold">-3.0%</span>
                                    </div>
                                    <div class="flex justify-between text-xs font-mono">
                                        <span class="text-slate-500">Toma de Beneficios (Take Profit):</span>
                                        <span class="text-slate-300 font-extrabold">+1.2%</span>
                                    </div>
                                    <div class="flex justify-between text-xs font-mono">
                                        <span class="text-slate-500">Detener Pérdidas (Stop Loss):</span>
                                        <span class="text-slate-300 font-extrabold">-1.5%</span>
                                    </div>
                                    <div class="flex justify-between text-xs font-mono">
                                        <span class="text-slate-500">Tamaño de Orden:</span>
                                        <span class="text-slate-300 font-extrabold">$100.00</span>
                                    </div>
                                    <div class="flex justify-between text-xs font-mono">
                                        <span class="text-slate-500">Límite Diario:</span>
                                        <span class="text-slate-300 font-extrabold">$200.00</span>
                                    </div>
                                </div>
                                <div class="p-3 bg-emerald-500/5 rounded-xl border border-emerald-500/10 text-[10px] text-slate-400 leading-relaxed font-medium">
                                    <strong>Variación automática:</strong> El bot esperará caídas del 3.0% antes de comprar, y cerrará posiciones rápidamente.
                                </div>
                            </div>
                            <div class="pt-5">
                                <form action="{{ route('profile.update-risk-profile') }}" method="POST" onsubmit="return confirm('¿Confirmas que deseas aplicar el perfil Conservador? Esto reescribirá instantáneamente tus parámetros de bot real.')">
                                    @csrf
                                    <input type="hidden" name="risk_profile" value="conservative">
                                    <button type="submit" 
                                            {{ $isConservativeActive ? 'disabled' : '' }}
                                            class="w-full py-2.5 rounded-xl text-xs font-extrabold uppercase tracking-wider transition cursor-pointer disabled:opacity-40 disabled:cursor-default flex items-center justify-center gap-1.5 {{ $isConservativeActive ? 'bg-slate-800 text-slate-500' : 'bg-emerald-600 hover:bg-emerald-500 text-white shadow-md shadow-emerald-700/10' }}">
                                        {{ $isConservativeActive ? 'Perfil Activo' : 'Activar Conservador' }}
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- CUSTOM CONFIG CARD -->
                        <div class="glass-panel rounded-2xl p-5 border flex flex-col justify-between transition-all duration-300 {{ $isCustomActive ? 'bg-indigo-950/15 border-indigo-500/30 shadow-lg shadow-indigo-550/5 ring-1 ring-indigo-500/20' : 'bg-slate-950/40 border-slate-900/60 hover:bg-slate-950/60' }}">
                            <div class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold uppercase tracking-wider text-indigo-400 flex items-center gap-1.5">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25V9m10.5 0a2.25 2.25 0 0 1 2.25 2.25v6.75a2.25 2.25 0 0 1-2.25 2.25h-10.5a2.25 2.25 0 0 1-2.25-2.25v-6.75a2.25 2.25 0 0 1 2.25-2.25M10.5 9V5.25a.75.75 0 0 0-.75-.75h-3a.75.75 0 0 0-.75.75V9" />
                                        </svg>
                                        Configuración Actual
                                    </span>
                                    @if($isCustomActive)
                                        <span class="text-[9px] font-black uppercase tracking-wider px-2 py-0.5 rounded bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">ACTIVO</span>
                                    @endif
                                </div>
                                <p class="text-xs text-slate-300 leading-relaxed font-medium">
                                    Muestra tus parámetros actuales de la cuenta real. Si no coinciden con los preajustes conservador, arriesgado o extremo, se muestran como personalizados.
                                </p>
                                <div class="bg-black/40 rounded-xl p-3.5 border border-slate-900/60 space-y-2">
                                    <div class="flex justify-between text-xs font-mono">
                                        <span class="text-slate-500">Caída de Compra:</span>
                                        <span class="text-indigo-400 font-extrabold">{{ Auth::user()->live_bot_buy_threshold }}%</span>
                                    </div>
                                    <div class="flex justify-between text-xs font-mono">
                                        <span class="text-slate-500">Toma de Beneficios (Take Profit):</span>
                                        <span class="text-indigo-400 font-extrabold">+{{ Auth::user()->live_bot_take_profit }}%</span>
                                    </div>
                                    <div class="flex justify-between text-xs font-mono">
                                        <span class="text-slate-500">Detener Pérdidas (Stop Loss):</span>
                                        <span class="text-indigo-400 font-extrabold">{{ Auth::user()->live_bot_stop_loss }}%</span>
                                    </div>
                                    <div class="flex justify-between text-xs font-mono">
                                        <span class="text-slate-500">Tamaño de Orden:</span>
                                        <span class="text-indigo-400 font-extrabold">${{ number_format(Auth::user()->live_bot_order_size, 2) }}</span>
                                    </div>
                                    <div class="flex justify-between text-xs font-mono">
                                        <span class="text-slate-500">Límite Diario:</span>
                                        <span class="text-indigo-400 font-extrabold">${{ number_format(Auth::user()->live_daily_spend_limit, 2) }}</span>
                                    </div>
                                </div>
                                <div class="p-3 bg-indigo-500/5 rounded-xl border border-indigo-500/10 text-[10px] text-slate-400 leading-relaxed font-medium">
                                    Para ajustar estos parámetros manualmente, puedes dirigirte en cualquier momento a la pestaña de ajustes en tu perfil.
                                </div>
                            </div>
                            <div class="pt-5">
                                <a href="{{ route('profile.edit') }}" class="w-full py-2.5 rounded-xl text-xs font-extrabold uppercase tracking-wider transition flex items-center justify-center gap-1.5 bg-slate-800 hover:bg-slate-700 text-slate-200 hover:text-white border border-slate-700/50">
                                    Modificar en Perfil
                                </a>
                            </div>
                        </div>

                        <!-- ARRIESGADO CARD -->
                        <div class="glass-panel rounded-2xl p-5 border flex flex-col justify-between transition-all duration-300 {{ $isRiskyActive ? 'bg-rose-950/15 border-rose-500/30 shadow-lg shadow-rose-550/5 ring-1 ring-rose-500/20' : 'bg-slate-950/40 border-slate-900/60 hover:bg-slate-950/60' }}">
                            <div class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold uppercase tracking-wider text-rose-400 flex items-center gap-1.5">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4">
                                            <path fill-rule="evenodd" d="M9.401 3.003c1.155-2 4.043-2 5.197 0l7.355 12.748c1.154 2-.29 4.5-2.599 4.5H4.645c-2.309 0-3.753-2.5-2.598-4.5L9.4 3.003ZM12 8.25a.75.75 0 0 1 .75.75v3.75a.75.75 0 0 1-1.5 0V9a.75.75 0 0 1 .75-.75Zm0 8.25a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Z" clip-rule="evenodd" />
                                        </svg>
                                        Arriesgado
                                    </span>
                                    @if($isRiskyActive)
                                        <span class="text-[9px] font-black uppercase tracking-wider px-2 py-0.5 rounded bg-rose-500/20 text-rose-300 border border-rose-500/30">ACTIVO</span>
                                    @endif
                                </div>
                                <p class="text-xs text-slate-300 leading-relaxed font-medium">
                                    Cambia los límites para arriesgar más las inversiones automáticamente. La volatilidad y el tamaño de las órdenes aumentarán considerablemente para buscar mayores rendimientos.
                                </p>
                                <div class="bg-black/40 rounded-xl p-3.5 border border-slate-900/60 space-y-2">
                                    <div class="flex justify-between text-xs font-mono">
                                        <span class="text-slate-500">Caída de Compra:</span>
                                        <span class="text-slate-300 font-extrabold">-0.5%</span>
                                    </div>
                                    <div class="flex justify-between text-xs font-mono">
                                        <span class="text-slate-500">Toma de Beneficios (Take Profit):</span>
                                        <span class="text-slate-300 font-extrabold">+10.0%</span>
                                    </div>
                                    <div class="flex justify-between text-xs font-mono">
                                        <span class="text-slate-500">Detener Pérdidas (Stop Loss):</span>
                                        <span class="text-slate-300 font-extrabold">-8.0%</span>
                                    </div>
                                    <div class="flex justify-between text-xs font-mono">
                                        <span class="text-slate-300 font-extrabold">Tamaño de Orden:</span>
                                        <span class="text-slate-300 font-extrabold">$1,000.00</span>
                                    </div>
                                    <div class="flex justify-between text-xs font-mono">
                                        <span class="text-slate-500">Límite Diario:</span>
                                        <span class="text-slate-300 font-extrabold">$3,000.00</span>
                                    </div>
                                </div>
                                <div class="p-3 bg-rose-500/5 rounded-xl border border-rose-500/10 text-[10px] text-slate-400 leading-relaxed font-medium">
                                    <strong>Variación automática:</strong> El bot comprará con caídas leves del 0.5%, usará órdenes grandes y buscará subidas del 10%.
                                </div>
                            </div>
                            <div class="pt-5">
                                <form action="{{ route('profile.update-risk-profile') }}" method="POST" onsubmit="return confirm('ATENCIÓN: Estás a punto de aplicar el perfil Arriesgado. El tamaño de orden subirá a $1,000.00 y el límite diario a $3,000.00 con Stop Loss del -8.0%. Esto implica un alto riesgo de pérdidas. ¿Confirmas que deseas aplicar este perfil?')">
                                    @csrf
                                    <input type="hidden" name="risk_profile" value="risky">
                                    <button type="submit" 
                                            {{ $isRiskyActive ? 'disabled' : '' }}
                                            class="w-full py-2.5 rounded-xl text-xs font-extrabold uppercase tracking-wider transition cursor-pointer disabled:opacity-40 disabled:cursor-default flex items-center justify-center gap-1.5 {{ $isRiskyActive ? 'bg-slate-800 text-slate-500' : 'bg-rose-650 hover:bg-rose-550 text-white shadow-md shadow-rose-700/10 border border-rose-500/30' }}">
                                        {{ $isRiskyActive ? 'Perfil Activo' : 'Activar Arriesgado' }}
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- EXTREMO CARD -->
                        <div class="glass-panel rounded-2xl p-5 border flex flex-col justify-between transition-all duration-300 {{ $isExtremeActive ? 'bg-fuchsia-950/15 border-fuchsia-500/30 shadow-lg shadow-fuchsia-550/5 ring-1 ring-fuchsia-500/20' : 'bg-slate-950/40 border-slate-900/60 hover:bg-slate-950/60' }}">
                            <div class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold uppercase tracking-wider text-fuchsia-400 flex items-center gap-1.5">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4">
                                            <path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25ZM12.75 6a.75.75 0 0 0-1.5 0v6c0 .414.336.75.75.75h4.5a.75.75 0 0 0 0-1.5h-3.75V6ZM12 18.75a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5Z" clip-rule="evenodd" />
                                        </svg>
                                        Extremo
                                    </span>
                                    @if($isExtremeActive)
                                        <span class="text-[9px] font-black uppercase tracking-wider px-2 py-0.5 rounded bg-fuchsia-500/20 text-fuchsia-300 border border-fuchsia-500/30">ACTIVO</span>
                                    @endif
                                </div>
                                <p class="text-xs text-slate-300 leading-relaxed font-medium">
                                    Configuración de riesgo extremadamente alta. El bot operará con rangos de stop loss muy profundos y gran tamaño de órdenes para maximizar potenciales beneficios.
                                </p>
                                <div class="bg-black/40 rounded-xl p-3.5 border border-slate-900/60 space-y-2">
                                    <div class="flex justify-between text-xs font-mono">
                                        <span class="text-slate-500">Caída de Compra:</span>
                                        <span class="text-slate-300 font-extrabold">-0.2%</span>
                                    </div>
                                    <div class="flex justify-between text-xs font-mono">
                                        <span class="text-slate-500">Toma de Beneficios (Take Profit):</span>
                                        <span class="text-slate-300 font-extrabold">+20.0%</span>
                                    </div>
                                    <div class="flex justify-between text-xs font-mono">
                                        <span class="text-slate-500">Detener Pérdidas (Stop Loss):</span>
                                        <span class="text-slate-300 font-extrabold">-15.0%</span>
                                    </div>
                                    <div class="flex justify-between text-xs font-mono">
                                        <span class="text-slate-500">Tamaño de Orden:</span>
                                        <span class="text-slate-300 font-extrabold">$2,000.00</span>
                                    </div>
                                    <div class="flex justify-between text-xs font-mono">
                                        <span class="text-slate-500">Límite Diario:</span>
                                        <span class="text-slate-300 font-extrabold">$5,000.00</span>
                                    </div>
                                </div>
                                <div class="p-3 bg-fuchsia-500/5 rounded-xl border border-fuchsia-500/10 text-[10px] text-slate-400 leading-relaxed font-medium">
                                    <strong>Variación automática:</strong> El bot comprará casi de inmediato ante mínimas caídas, usará órdenes muy grandes y buscará subidas del 20%.
                                </div>
                            </div>
                            <div class="pt-5">
                                <form action="{{ route('profile.update-risk-profile') }}" method="POST" onsubmit="return confirm('PELIGRO CRÍTICO: Estás a punto de aplicar el perfil Extremo. El tamaño de orden subirá a $2,000.00 y el límite diario a $5,000.00 con un Stop Loss muy amplio del -15.0%. Esto puede ocasionar grandes pérdidas rápidamente. ¿Estás totalmente seguro de confirmar?')">
                                    @csrf
                                    <input type="hidden" name="risk_profile" value="extreme">
                                    <button type="submit" 
                                            {{ $isExtremeActive ? 'disabled' : '' }}
                                            class="w-full py-2.5 rounded-xl text-xs font-extrabold uppercase tracking-wider transition cursor-pointer disabled:opacity-40 disabled:cursor-default flex items-center justify-center gap-1.5 {{ $isExtremeActive ? 'bg-slate-800 text-slate-500' : 'bg-fuchsia-600 hover:bg-fuchsia-500 text-white shadow-md shadow-fuchsia-700/10 border border-fuchsia-500/30' }}">
                                        {{ $isExtremeActive ? 'Perfil Activo' : 'Activar Extremo' }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="p-4 bg-amber-500/10 rounded-2xl border border-amber-500/20 text-xs text-amber-300 leading-relaxed flex gap-3">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5 shrink-0 text-amber-400">
                            <path fill-rule="evenodd" d="M9.401 3.003c1.155-2 4.043-2 5.197 0l7.355 12.748c1.154 2-.29 4.5-2.599 4.5H4.645c-2.309 0-3.753-2.5-2.598-4.5L9.4 3.003ZM12 8.25a.75.75 0 0 1 .75.75v3.75a.75.75 0 0 1-1.5 0V9a.75.75 0 0 1 .75-.75Zm0 8.25a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Z" clip-rule="evenodd" />
                        </svg>
                        <div>
                            <strong class="block mb-0.5 font-bold">Advertencia de Riesgo y Operaciones Financieras:</strong>
                            El trading automático en los mercados financieros conlleva un nivel elevado de riesgo. La aplicación de cualquier perfil de riesgo sugerido modifica inmediatamente el comportamiento del bot en vivo en tu bróker de Alpaca. Asegúrate de comprender los riesgos involucrados y de contar con los fondos suficientes.
                        </div>
                    </div>
                </div>
            </div>
            @endif
            <!-- TAB 4: LIVE MARKETS -->
            <div x-show="activeTab === 'markets'" class="space-y-6" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" style="display: none;">
                <div class="space-y-4">
                    <h2 class="text-lg font-extrabold text-white flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-indigo-400">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                        </svg>
                        Mercados en Vivo y Cotizaciones de Referencia
                    </h2>
                    <p class="text-xs text-slate-400 leading-relaxed max-w-3xl">
                        A continuación se muestran los principales índices globales, divisas, materias primas y criptomonedas. Estos datos se actualizan en tiempo real a través de Yahoo Finance y sirven como barómetro de la tendencia macroeconómica.
                    </p>

                    @if(!empty($tickerQuotes))
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach($tickerQuotes as $symbol => $quote)
                                @php
                                    $isPositive = ($quote['changePercent'] ?? 0) >= 0;
                                    $colorClass = $isPositive ? 'text-green-400' : 'text-red-400';
                                    $bgClass = $isPositive ? 'bg-green-500/5 border-green-500/10' : 'bg-red-500/5 border-red-500/10';
                                    $symbolClean = str_replace(['=X', '^'], '', $symbol);
                                    $meta = $tickerMetadata[$symbol] ?? ['name' => $symbolClean, 'desc' => ''];
                                @endphp
                                <a href="{{ route('assets.show', $symbol) }}" class="glass-panel glass-panel-hover rounded-2xl p-5 block transition duration-200 border relative overflow-hidden group {{ $bgClass }}">
                                    <div class="absolute right-4 top-4 text-slate-700/30 group-hover:text-indigo-500/20 transition-colors duration-200">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-8 h-8">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.307a11.95 11.95 0 0 0 5.814-5.519l2.74-1.22m0 0-5.94-2.28m5.94 2.28-2.28 5.941" />
                                        </svg>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="font-extrabold text-white text-base">{{ $meta['name'] }}</span>
                                        <span class="text-[10px] text-slate-500 font-bold uppercase tracking-wider bg-slate-950/40 px-2 py-0.5 rounded border border-slate-900/60">
                                            {{ $symbolClean }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-slate-400 mt-2 line-clamp-2 h-8 leading-relaxed">
                                        {{ $meta['desc'] }}
                                    </p>
                                    <div class="mt-4 pt-4 border-t border-slate-900/40 flex items-center justify-between">
                                        <span class="text-lg font-mono font-extrabold text-white">
                                            ${{ number_format($quote['price'] ?? 0, 2) }}
                                        </span>
                                        <span class="flex items-center gap-1 font-bold text-sm {{ $colorClass }}">
                                            <span>{{ $isPositive ? '▲' : '▼' }}</span>
                                            <span>{{ number_format(abs($quote['changePercent'] ?? 0), 2) }}%</span>
                                        </span>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <div class="glass-panel rounded-2xl p-6 text-center text-slate-500">
                            No hay cotizaciones de mercado en vivo disponibles en este momento.
                        </div>
                    @endif
                </div>
            </div>
            
            @if(!$isPaper)
            <!-- TAB 5: DEPOSIT FUNDS INSTRUCTIONS -->
            <div x-show="activeTab === 'deposit_funds'" class="space-y-6" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" style="display: none;">
                <div class="glass-panel rounded-2xl p-6 bg-[#060e15]/20 border border-slate-900/60 shadow-lg space-y-6">
                    <div>
                        <h2 class="text-lg font-extrabold text-white flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-indigo-400">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.33l-7.5-5-7.5 5V21m-2.25 0h24" />
                            </svg>
                            Instrucciones para Ingresar Fondos Reales en Alpaca
                        </h2>
                        <p class="text-xs text-slate-400 leading-relaxed mt-1">
                            Sigue esta guía paso a paso para añadir dinero fiduciario (euros o dólares) a tu saldo real del bróker y permitir que el bot pueda operar.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Step 1 -->
                        <div class="p-5 bg-slate-950/40 rounded-2xl border border-slate-900/60 flex flex-col justify-between">
                            <div class="space-y-3">
                                <div class="flex items-center gap-3">
                                    <span class="w-7 h-7 rounded-full bg-indigo-500/10 border border-indigo-500/30 flex items-center justify-center text-xs font-black text-indigo-400 shrink-0">1</span>
                                    <h3 class="text-xs font-black text-indigo-300 uppercase tracking-wider">Accede a Alpaca</h3>
                                </div>
                                <p class="text-[11px] text-slate-400 leading-relaxed font-medium">
                                    Inicia sesión en tu cuenta de bróker en el portal oficial de Alpaca Markets. Recuerda cambiar la vista de "Paper" a "Live" en tu panel para ver la cuenta real.
                                </p>
                            </div>
                        </div>

                        <!-- Step 2 -->
                        <div class="p-5 bg-slate-950/40 rounded-2xl border border-slate-900/60 flex flex-col justify-between">
                            <div class="space-y-3">
                                <div class="flex items-center gap-3">
                                    <span class="w-7 h-7 rounded-full bg-indigo-500/10 border border-indigo-500/30 flex items-center justify-center text-xs font-black text-indigo-400 shrink-0">2</span>
                                    <h3 class="text-xs font-black text-indigo-300 uppercase tracking-wider">Obtén datos de Banco</h3>
                                </div>
                                <p class="text-[11px] text-slate-400 leading-relaxed font-medium">
                                    Ve a la sección de <strong>"Banking / Transfers"</strong> y selecciona <strong>"Deposit"</strong>. Elige transferencia <strong>SEPA</strong> (para euros) o <strong>Wire Transfer</strong> (para dólares) y anota el número de IBAN/cuenta, el código de banco y el beneficiario.
                                </p>
                            </div>
                        </div>

                        <!-- Step 3 -->
                        <div class="p-5 bg-slate-950/40 rounded-2xl border border-slate-900/60 flex flex-col justify-between">
                            <div class="space-y-3">
                                <div class="flex items-center gap-3">
                                    <span class="w-7 h-7 rounded-full bg-indigo-500/10 border border-indigo-500/30 flex items-center justify-center text-xs font-black text-indigo-400 shrink-0">3</span>
                                    <h3 class="text-xs font-black text-indigo-300 uppercase tracking-wider">Realiza la transferencia</h3>
                                </div>
                                <p class="text-[11px] text-slate-400 leading-relaxed font-medium">
                                    Envía los fondos desde tu banco nacional. <strong class="text-amber-400">IMPORTANTE:</strong> Asegúrate de incluir el <strong>código de referencia</strong> personal que te da Alpaca en el campo "Concepto" de la transferencia para que identifiquen tu depósito.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Warning Alert block -->
                    <div class="p-4 bg-amber-500/10 rounded-xl border border-amber-500/20 text-xs text-amber-300 leading-relaxed flex gap-3">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5 shrink-0 text-amber-400">
                            <path fill-rule="evenodd" d="M9.401 3.003c1.155-2 4.043-2 5.197 0l7.355 12.748c1.154 2-.29 4.5-2.599 4.5H4.645c-2.309 0-3.753-2.5-2.598-4.5L9.4 3.003ZM12 8.25a.75.75 0 0 1 .75.75v3.75a.75.75 0 0 1-1.5 0V9a.75.75 0 0 1 .75-.75Zm0 8.25a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Z" clip-rule="evenodd" />
                        </svg>
                        <div>
                            <strong class="block mb-0.5 font-bold">Nota de procesamiento:</strong>
                            Las transferencias bancarias internacionales (Wire) suelen tardar de 1 a 3 días hábiles en aparecer en tu saldo de Alpaca. Las transferencias SEPA europeas suelen acreditarse en 24-48 horas hábiles.
                        </div>
                    </div>

                    <!-- Action Button -->
                    <div class="pt-2 text-center md:text-left">
                        <a href="https://app.alpaca.markets/" target="_blank" rel="noopener noreferrer" 
                           class="inline-flex items-center gap-2 px-5 py-3 rounded-xl text-xs font-black text-white bg-emerald-600 hover:bg-emerald-500 transition duration-150 shadow-lg shadow-emerald-600/10">
                            <span>Efectuar transferencia en Alpaca</span>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
            @endif
        </div>
    @endif

</div>
@endsection
