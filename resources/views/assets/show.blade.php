@extends('layouts.layout')

@section('title', ($assetData['longName'] ?? $assetData['symbol']) . ' | Gráfico y Cotización')

@section('content')
<div class="space-y-6" x-data="chartManager()">
    
    <!-- Navigation back & Watchlist Toggle -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-400 hover:text-white transition duration-150">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
            </svg>
            Volver a Mercados
        </a>

        @auth
            @if($isWatched)
                <form action="{{ route('watchlist.destroy', $assetData['symbol']) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold bg-red-500/10 text-red-400 border border-red-500/20 hover:bg-red-500/20 transition cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4 text-red-400">
                            <path fill-rule="evenodd" d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.006 5.404.434c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.434 2.082-5.005Z" clip-rule="evenodd" />
                        </svg>
                        Quitar de Favoritos
                    </button>
                </form>
            @else
                <form action="{{ route('watchlist.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="symbol" value="{{ $assetData['symbol'] }}">
                    <input type="hidden" name="name" value="{{ $assetData['longName'] }}">
                    <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold bg-slate-900 border border-slate-800 text-indigo-400 hover:border-slate-700 hover:text-indigo-300 transition cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 text-indigo-400">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499c.153-.314.592-.314.745 0l1.98 4.012 4.426.642c.348.05.487.48.236.72l-3.2 3.12 1.054 4.407a.418.418 0 0 1-.606.44l-3.957-2.08-3.958 2.08a.418.418 0 0 1-.606-.44l1.054-4.406-3.2-3.12a.418.418 0 0 1 .236-.72l4.426-.642 1.98-4.012Z" />
                        </svg>
                        Añadir a Favoritos
                    </button>
                </form>
            @endif
        @else
            <div class="text-[11px] text-slate-500 font-medium">
                Inicia sesión para añadir este activo a tu watchlist.
            </div>
        @endauth
    </div>

    <!-- Details Header Grid (Title & Live Price) -->
    @php
        $isPositive = ($assetData['changePercent'] ?? 0) >= 0;
        $colorClass = $isPositive ? 'text-green-400' : 'text-red-400';
        $bgColorClass = $isPositive ? 'bg-green-500/10' : 'bg-red-500/10';
        $symbolClean = str_replace(['=X', '^'], '', $assetData['symbol']);
    @endphp
    <div class="glass-panel rounded-2xl p-6 lg:p-8 flex flex-col md:flex-row justify-between items-start md:items-center gap-6 shadow-xl">
        <div class="space-y-1">
            <div class="flex items-center gap-3">
                <h1 class="text-2xl lg:text-3xl font-extrabold text-white tracking-tight">{{ $symbolClean }}</h1>
                <span class="text-xs px-2.5 py-0.5 rounded-md bg-indigo-600/10 border border-indigo-500/25 text-indigo-400 uppercase font-bold tracking-wider">
                    {{ $assetData['exchange'] ?? 'Activo' }}
                </span>
            </div>
            <p class="text-sm text-slate-400 font-medium">{{ $assetData['longName'] ?? $assetData['shortName'] }}</p>
        </div>

        <div class="flex items-baseline gap-4">
            <span class="text-3xl lg:text-4xl font-extrabold text-white leading-none tracking-tight">
                ${{ number_format($assetData['price'] ?? 0, 2) }}
            </span>
            <div class="flex flex-col items-end">
                <span class="text-sm font-extrabold {{ $colorClass }}">
                    {{ $isPositive ? '+' : '' }}{{ number_format($assetData['change'] ?? 0, 2) }}
                </span>
                <span class="inline-block px-2 py-0.5 rounded-md text-xs font-bold {{ $colorClass }} {{ $bgColorClass }} mt-1">
                    {{ $isPositive ? '+' : '' }}{{ number_format($assetData['changePercent'] ?? 0, 2) }}%
                </span>
            </div>
        </div>
    </div>

    <!-- Chart & Controls Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Interactive Chart Canvas (Col Span 2) -->
        <div class="lg:col-span-2 glass-panel rounded-2xl p-6 shadow-xl flex flex-col space-y-4">
            <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-900 pb-4">
                
                <!-- Timeframes -->
                <div class="flex items-center bg-slate-950/60 border border-slate-900 p-1 rounded-xl">
                    @foreach(['1d' => '1D', '5d' => '5D', '1mo' => '1M', '6mo' => '6M', '1y' => '1Y', '5y' => '5Y', 'max' => 'MAX'] as $r => $label)
                        <button 
                            @click="changeRange('{{ $r }}')" 
                            :class="range === '{{ $r }}' ? 'bg-indigo-600 text-white shadow-md' : 'text-slate-400 hover:text-slate-200'" 
                            class="px-3.5 py-1.5 text-xs font-bold rounded-lg transition duration-200 cursor-pointer"
                        >
                            {{ $label }}
                        </button>
                    @endforeach
                </div>

                <!-- Chart Type Selector -->
                <div class="flex items-center bg-slate-950/60 border border-slate-900 p-1 rounded-xl">
                    <button 
                        @click="setChartType('area')" 
                        :class="chartType === 'area' ? 'bg-indigo-600 text-white' : 'text-slate-400 hover:text-slate-200'"
                        class="px-3 py-1.5 text-xs font-semibold rounded-lg transition cursor-pointer"
                    >
                        Área
                    </button>
                    <button 
                        @click="setChartType('candlestick')" 
                        :class="chartType === 'candlestick' ? 'bg-indigo-600 text-white' : 'text-slate-400 hover:text-slate-200'"
                        class="px-3 py-1.5 text-xs font-semibold rounded-lg transition cursor-pointer"
                    >
                        Velas
                    </button>
                </div>
            </div>

            <!-- Chart Mounting Div -->
            <div class="relative w-full h-[400px] bg-[#070913]/30 rounded-xl overflow-hidden">
                <div id="chart-container" class="w-full h-full"></div>
                <!-- Loading Overlay -->
                <div x-show="loading" class="absolute inset-0 bg-slate-950/75 flex items-center justify-center z-10 transition duration-200">
                    <div class="flex flex-col items-center gap-3">
                        <div class="w-8 h-8 rounded-full border-2 border-indigo-600 border-t-transparent animate-spin"></div>
                        <span class="text-xs text-slate-400 font-medium">Cargando gráfico...</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Trading Panel / Sidebar -->
        <div class="space-y-6">
            
            @auth
            @php
                $symbol = $assetData['symbol'] ?? '';
                $isTradeable = !str_starts_with($symbol, '^') && !str_contains($symbol, '=X') && !str_contains($symbol, '=F');
                
                // Mapeo de alternativas de ETFs negociables para símbolos no negociables populares
                $alternativesMap = [
                    // Índices
                    '^IBEX' => [
                        ['symbol' => 'EWP', 'name' => 'iShares MSCI Spain ETF', 'desc' => 'Replica el rendimiento de empresas españolas de media y gran capitalización.']
                    ],
                    '^GSPC' => [
                        ['symbol' => 'SPY', 'name' => 'SPDR S&P 500 ETF Trust', 'desc' => 'Replica las 500 mayores empresas de EE.UU.'],
                        ['symbol' => 'VOO', 'name' => 'Vanguard S&P 500 ETF', 'desc' => 'ETF de bajo coste que replica el S&P 500.']
                    ],
                    '^IXIC' => [
                        ['symbol' => 'QQQ', 'name' => 'Invesco QQQ Trust', 'desc' => 'Sigue las 100 mayores empresas no financieras del Nasdaq.'],
                    ],
                    '^NDX' => [
                        ['symbol' => 'QQQ', 'name' => 'Invesco QQQ Trust', 'desc' => 'Sigue las 100 mayores empresas no financieras del Nasdaq.'],
                    ],
                    '^DJI' => [
                        ['symbol' => 'DIA', 'name' => 'SPDR Dow Jones Industrial Average ETF', 'desc' => 'Replica las 30 corporaciones industriales del Dow Jones.']
                    ],
                    '^FTSE' => [
                        ['symbol' => 'EWU', 'name' => 'iShares MSCI United Kingdom ETF', 'desc' => 'Acciones de gran y mediana capitalización del Reino Unido.']
                    ],
                    '^GDAXI' => [
                        ['symbol' => 'EWG', 'name' => 'iShares MSCI Germany ETF', 'desc' => 'Acciones alemanas líderes de gran y mediana capitalización.']
                    ],
                    '^N225' => [
                        ['symbol' => 'EWJ', 'name' => 'iShares MSCI Japan ETF', 'desc' => 'Sigue una amplia gama de acciones líderes en Japón.']
                    ],
                    '^FCHI' => [
                        ['symbol' => 'EWQ', 'name' => 'iShares MSCI France ETF', 'desc' => 'Acciones francesas líderes de gran y mediana capitalización.']
                    ],
                    '^STOXX50E' => [
                        ['symbol' => 'FEZ', 'name' => 'SPDR EURO STOXX 50 ETF', 'desc' => 'Las 50 mayores empresas líderes de la Eurozona.']
                    ],
                    '^HSI' => [
                        ['symbol' => 'EWH', 'name' => 'iShares MSCI Hong Kong ETF', 'desc' => 'Acciones representativas del mercado de Hong Kong.']
                    ],
                    
                    // Forex / Divisas
                    'EURUSD=X' => [
                        ['symbol' => 'FXE', 'name' => 'Invesco Euro Currency Trust', 'desc' => 'Sigue de cerca el comportamiento del euro frente al dólar.']
                    ],
                    
                    // Materias Primas
                    'GC=F' => [
                        ['symbol' => 'GLD', 'name' => 'SPDR Gold Shares', 'desc' => 'Sigue el precio del lingote de oro físico de forma directa.'],
                        ['symbol' => 'IAU', 'name' => 'iShares Gold Trust', 'desc' => 'Alternativa de bajo coste para seguir el precio del oro.']
                    ],
                    'CL=F' => [
                        ['symbol' => 'USO', 'name' => 'United States Oil Fund', 'desc' => 'Sigue el precio del petróleo crudo ligero dulce (WTI).']
                    ],
                    'BZ=F' => [
                        ['symbol' => 'BNO', 'name' => 'United States Brent Oil Fund', 'desc' => 'Sigue el precio del petróleo de referencia global Brent.']
                    ],
                    'LGO=F' => [
                        ['symbol' => 'BNO', 'name' => 'United States Brent Oil Fund', 'desc' => 'Sigue el petróleo Brent, el proxy más cercano negociable.']
                    ],
                    'SI=F' => [
                        ['symbol' => 'SLV', 'name' => 'iShares Silver Trust', 'desc' => 'Sigue el precio de los lingotes de plata física.']
                    ],
                    'NG=F' => [
                        ['symbol' => 'UNG', 'name' => 'United States Natural Gas Fund', 'desc' => 'Sigue el precio de los futuros de gas natural.']
                    ]
                ];
                
                $alternatives = $alternativesMap[$symbol] ?? [];
            @endphp
            <!-- Panel de Trading -->
            <div class="glass-panel rounded-2xl p-6 shadow-xl space-y-4">
                <h2 class="text-base font-extrabold text-white flex items-center gap-2 border-b border-slate-900 pb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-indigo-400">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L17.5 12M21 7.5H7.5" />
                    </svg>
                    Operar en Mercado (Alpaca)
                </h2>

                <!-- Flash session messages / errors -->
                @if(session('success'))
                    <div class="p-3 text-xs bg-green-500/10 border border-green-500/20 text-green-400 rounded-xl">
                        {!! session('success') !!}
                    </div>
                @endif
                @if($errors->any())
                    <div class="p-3 text-xs bg-red-500/10 border border-red-500/20 text-red-400 rounded-xl space-y-1">
                        @foreach($errors->all() as $err)
                            <div>{{ $err }}</div>
                        @endforeach
                    </div>
                @endif

                @if(!$isTradeable)
                    <!-- Non-tradeable Asset Notice -->
                    <div class="space-y-4 py-2 text-left">
                        <div class="p-4 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-amber-400 text-xs leading-relaxed space-y-2 font-medium">
                            <div class="font-bold flex items-center gap-1.5 text-white">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 text-amber-400">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                                </svg>
                                Activo No Operable
                            </div>
                            <p>
                                Este activo representativo (Índice bursátil, Divisa Forex o Materia Prima) se muestra únicamente como <strong>referencia informativa</strong> del mercado.
                            </p>
                            <p>
                                Tu bróker (Alpaca) solo soporta la compra y venta de <strong>acciones estadounidenses, ETFs y Criptomonedas principales</strong>.
                            </p>
                        </div>
                        
                        @if(!empty($alternatives))
                            <div class="space-y-3 pt-2">
                                <h3 class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Alternativas operables en Alpaca:</h3>
                                <div class="space-y-2">
                                    @foreach($alternatives as $alt)
                                        <a href="{{ route('asset.show', ['symbol' => $alt['symbol']]) }}" 
                                           class="block p-3 rounded-xl bg-slate-950/40 border border-slate-900/60 hover:border-indigo-500/60 hover:bg-slate-900/60 transition group">
                                            <div class="flex items-center justify-between">
                                                <div class="space-y-1 pr-2">
                                                    <div class="flex items-center gap-2">
                                                        <span class="text-xs font-extrabold text-indigo-400 group-hover:text-indigo-300 transition">{{ $alt['symbol'] }}</span>
                                                        <span class="text-[10px] font-semibold text-slate-300">{{ $alt['name'] }}</span>
                                                    </div>
                                                    <p class="text-[10px] text-slate-500 leading-normal">{{ $alt['desc'] }}</p>
                                                </div>
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 text-slate-500 group-hover:text-indigo-400 group-hover:translate-x-0.5 transition-all flex-shrink-0">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                                                </svg>
                                            </div>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <p class="text-[11px] text-slate-500 leading-normal">
                                Si deseas obtener exposición a este índice o invertir en el mercado correspondiente, puedes buscar e invertir en un ETF indexado compatible que cotice en la bolsa de EE.UU. (ej. ETFs de índices).
                            </p>
                        @endif
                    </div>
                @else
                    <form action="{{ route('trade.execute') }}" method="POST" class="space-y-4"
                         x-data="{ 
                             tradeType: 'market', 
                             investMode: 'amount', 
                             amount: '', 
                             qty: 1, 
                             limitPrice: '',
                             currentPrice: {{ $assetData['price'] ?? 0 }}, 
                             dailyLimit: {{ auth()->user()->daily_spend_limit ?? 5000.0 }},
                             spentToday: {{ auth()->user()->getDailySpent() ?? 0.0 }},
                             
                             get estimatedCost() {
                                 if (this.investMode === 'amount') {
                                     return parseFloat(this.amount) || 0;
                                 } else {
                                     let price = this.tradeType === 'limit' && this.limitPrice ? parseFloat(this.limitPrice) : this.currentPrice;
                                     return (parseFloat(this.qty) || 0) * price;
                                 }
                             },
                             
                             get computedQty() {
                                 if (this.investMode === 'qty') {
                                     return parseFloat(this.qty) || 0;
                                 } else {
                                     let price = this.tradeType === 'limit' && this.limitPrice ? parseFloat(this.limitPrice) : this.currentPrice;
                                     if (!price) return 0;
                                     let val = (parseFloat(this.amount) || 0) / price;
                                     return parseFloat(val.toFixed(6));
                                 }
                             }
                         }">
                        @csrf
                        <input type="hidden" name="symbol" value="{{ $assetData['symbol'] }}">
                        <input type="hidden" name="qty" :value="computedQty">

                        <!-- Order Type -->
                        <div class="space-y-1.5">
                            <label class="text-[10px] text-slate-500 font-bold uppercase tracking-wider">Tipo de Orden</label>
                            <select name="type" x-model="tradeType" class="w-full bg-slate-950/60 border border-slate-800 rounded-xl py-2 px-3 text-sm text-slate-200 focus:outline-none focus:border-indigo-500">
                                <option value="market">A Mercado (Market)</option>
                                <option value="limit">Límite (Limit)</option>
                            </select>
                            <p class="text-[10px] text-slate-400 leading-normal" x-show="tradeType === 'market'">
                                ℹ️ <strong>A Mercado:</strong> Compra o vende al instante al mejor precio del mercado hoy.
                            </p>
                            <p class="text-[10px] text-slate-400 leading-normal" x-show="tradeType === 'limit'">
                                ℹ️ <strong>Límite:</strong> Compra o vende únicamente si el precio del activo alcanza el precio límite que tú indiques.
                            </p>
                        </div>

                        <!-- Invest Mode Toggle -->
                        <div class="space-y-1.5">
                            <label class="text-[10px] text-slate-500 font-bold uppercase tracking-wider">Cómo quieres operar</label>
                            <div class="grid grid-cols-2 gap-2 p-1 rounded-xl bg-slate-950/80 border border-slate-900/60 shadow-inner">
                                <button type="button" 
                                        @click="investMode = 'amount'; qty = 1;"
                                        :class="investMode === 'amount' ? 'bg-indigo-600 text-white shadow-md' : 'text-slate-400 hover:text-slate-200'"
                                        class="py-1.5 rounded-lg text-xs font-bold transition duration-150 cursor-pointer">
                                    Monto en Dólares ($)
                                </button>
                                <button type="button" 
                                        @click="investMode = 'qty'; amount = '';"
                                        :class="investMode === 'qty' ? 'bg-indigo-600 text-white shadow-md' : 'text-slate-400 hover:text-slate-200'"
                                        class="py-1.5 rounded-lg text-xs font-bold transition duration-150 cursor-pointer">
                                    Número de Acciones
                                </button>
                            </div>
                        </div>

                        <!-- Input based on Mode -->
                        <div class="space-y-1.5" x-show="investMode === 'amount'" x-transition>
                            <label class="text-[10px] text-slate-500 font-bold uppercase tracking-wider">Monto a Invertir ($ USD)</label>
                            <input type="number" 
                                   x-model.number="amount" 
                                   min="1" 
                                   step="any" 
                                   placeholder="Ej: 500" 
                                   class="w-full bg-slate-950/60 border border-slate-800 rounded-xl py-2 px-3 text-sm text-slate-200 focus:outline-none focus:border-indigo-500">
                            <p class="text-[10px] text-slate-500 leading-normal">
                                Introduce cuántos dólares deseas gastar/invertir en este activo.
                            </p>
                        </div>

                        <div class="space-y-1.5" x-show="investMode === 'qty'" x-transition>
                            <label class="text-[10px] text-slate-500 font-bold uppercase tracking-wider">Cantidad (Acciones)</label>
                            <input type="number" 
                                   x-model.number="qty" 
                                   min="0.0001" 
                                   step="any" 
                                   placeholder="Ej: 1.5" 
                                   class="w-full bg-slate-950/60 border border-slate-800 rounded-xl py-2 px-3 text-sm text-slate-200 focus:outline-none focus:border-indigo-500">
                            <p class="text-[10px] text-slate-500 leading-normal">
                                Introduce el número exacto (o fracción) de acciones que quieres comprar o vender.
                            </p>
                        </div>

                        <!-- Limit Price (Only visible if type === limit) -->
                        <div class="space-y-1.5" x-show="tradeType === 'limit'" x-transition>
                            <label class="text-[10px] text-slate-500 font-bold uppercase tracking-wider">Precio Límite por Acción ($)</label>
                            <input type="number" 
                                   name="limit_price" 
                                   x-model.number="limitPrice" 
                                   step="0.01" 
                                   placeholder="Ej: {{ number_format($assetData['price'] ?? 0, 2, '.', '') }}" 
                                   class="w-full bg-slate-950/60 border border-slate-800 rounded-xl py-2 px-3 text-sm text-slate-200 focus:outline-none focus:border-indigo-500">
                            <p class="text-[10px] text-slate-500 leading-normal">
                                La orden solo se activará si el precio del activo es igual o mejor que este valor unitario.
                            </p>
                        </div>

                        <!-- Summary & Warnings -->
                        <div class="space-y-2">
                            <!-- Equivalent shares preview when investing by amount -->
                            <div x-show="investMode === 'amount' && amount > 0" class="p-3 bg-slate-950/20 rounded-xl border border-slate-900/40 flex justify-between items-center text-xs">
                                <span class="text-slate-500 font-medium">Equivale aprox. a</span>
                                <span class="font-extrabold text-indigo-400" x-text="computedQty + ' acciones'"></span>
                            </div>

                            <!-- Cost Estimate -->
                            <div class="p-3 bg-slate-950/40 rounded-xl border border-slate-900 flex justify-between items-center text-xs">
                                <span class="text-slate-500 font-semibold">Costo Estimado</span>
                                <span class="font-extrabold text-slate-200" x-text="'$' + estimatedCost.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})"></span>
                            </div>

                            <!-- Limit Exceeded Warning -->
                            <div x-show="estimatedCost > (dailyLimit - spentToday)" class="p-3.5 bg-red-500/10 border border-red-500/20 text-red-400 rounded-xl text-[11px] leading-normal font-bold">
                                ⚠️ La compra manual excede tu límite diario de gasto disponible (${(dailyLimit - spentToday).toFixed(2)} de un total de ${dailyLimit.toFixed(2)}).
                            </div>
                        </div>

                        <!-- Action buttons -->
                        <div class="grid grid-cols-2 gap-3 pt-2">
                            <button type="submit" name="side" value="buy" 
                                    :disabled="estimatedCost > (dailyLimit - spentToday) || estimatedCost <= 0"
                                    :class="estimatedCost > (dailyLimit - spentToday) || estimatedCost <= 0 ? 'opacity-40 cursor-not-allowed bg-slate-800 text-slate-500' : 'bg-green-600 hover:bg-green-500 text-white shadow-green-600/10 cursor-pointer shadow-lg'"
                                    class="py-2.5 rounded-xl text-xs font-extrabold transition text-center uppercase">
                                COMPRAR
                            </button>
                            <button type="submit" name="side" value="sell" 
                                    :disabled="estimatedCost <= 0"
                                    :class="estimatedCost <= 0 ? 'opacity-40 cursor-not-allowed bg-slate-800 text-slate-500' : 'bg-red-600 hover:bg-red-500 text-white shadow-red-600/10 cursor-pointer shadow-lg'"
                                    class="py-2.5 rounded-xl text-xs font-extrabold transition text-center uppercase">
                                VENDER
                            </button>
                        </div>
                    </form>
                @endif
            </div>
            </div>
            @endauth

            <!-- Key stats sidebar -->
            <div class="glass-panel rounded-2xl p-6 shadow-xl space-y-6">
            <h2 class="text-base font-extrabold text-white flex items-center gap-2 border-b border-slate-900 pb-3">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-indigo-400">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6a7.5 7.5 0 1 0 7.5 7.5h-7.5V6Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5H21A7.5 7.5 0 0 0 13.5 3v7.5Z" />
                </svg>
                Métricas Clave
            </h2>

            <div class="grid grid-cols-2 gap-y-4 gap-x-6">
                <div class="flex flex-col gap-1">
                    <span class="text-[10px] text-slate-500 font-bold uppercase tracking-wider">Máximo del Día</span>
                    <span class="text-sm font-extrabold text-slate-100">${{ number_format($assetData['dayHigh'] ?? 0, 2) }}</span>
                </div>
                <div class="flex flex-col gap-1">
                    <span class="text-[10px] text-slate-500 font-bold uppercase tracking-wider">Mínimo del Día</span>
                    <span class="text-sm font-extrabold text-slate-100">${{ number_format($assetData['dayLow'] ?? 0, 2) }}</span>
                </div>
                <div class="flex flex-col gap-1 border-t border-slate-900 pt-3">
                    <span class="text-[10px] text-slate-500 font-bold uppercase tracking-wider">Máx. 52 Semanas</span>
                    <span class="text-sm font-extrabold text-slate-100">${{ number_format($assetData['fiftyTwoWeekHigh'] ?? 0, 2) }}</span>
                </div>
                <div class="flex flex-col gap-1 border-t border-slate-900 pt-3">
                    <span class="text-[10px] text-slate-500 font-bold uppercase tracking-wider">Mín. 52 Semanas</span>
                    <span class="text-sm font-extrabold text-slate-100">${{ number_format($assetData['fiftyTwoWeekLow'] ?? 0, 2) }}</span>
                </div>
                <div class="flex flex-col gap-1 border-t border-slate-900 pt-3">
                    <span class="text-[10px] text-slate-500 font-bold uppercase tracking-wider">Volumen</span>
                    <span class="text-sm font-extrabold text-slate-100">{{ number_format($assetData['volume'] ?? 0) }}</span>
                </div>
                <div class="flex flex-col gap-1 border-t border-slate-900 pt-3">
                    <span class="text-[10px] text-slate-500 font-bold uppercase tracking-wider">Divisa</span>
                    <span class="text-sm font-extrabold text-slate-100 uppercase">{{ $assetData['currency'] ?? 'USD' }}</span>
                </div>
            </div>
            
            <div class="border-t border-slate-900 pt-4 text-xs text-slate-500 leading-relaxed">
                Datos actualizados para {{ $assetData['longName'] }}. Los máximos y mínimos reflejan la banda de negociación intradía y anual de la bolsa de valores origen.
            </div>
        </div>
        </div>

    </div>
</div>

<script>
    function chartManager() {
        return {
            symbol: '{{ $assetData['symbol'] }}',
            range: '1d',
            chartType: 'area', // or candlestick
            loading: true,

            init() {
                // Initialize TradingView Chart on mount
                const chartElement = document.getElementById('chart-container');
                if (!chartElement) return;
                
                const chart = LightweightCharts.createChart(chartElement, {
                    layout: {
                        background: { color: 'transparent' },
                        textColor: '#94a3b8',
                    },
                    grid: {
                        vertLines: { color: '#0f172a' },
                        horzLines: { color: '#0f172a' },
                    },
                    rightPriceScale: {
                        borderColor: '#1e293b',
                    },
                    timeScale: {
                        borderColor: '#1e293b',
                        timeVisible: true,
                    },
                    handleScroll: true,
                    handleScale: true,
                });
                
                // Store on DOM element to completely bypass Alpine reactivity proxies
                chartElement.__chart = chart;

                // Handle window resizing
                const resizeObserver = new ResizeObserver(entries => {
                    if (entries.length === 0 || !entries[0].contentRect) return;
                    const { width, height } = entries[0].contentRect;
                    chart.resize(width, height);
                });
                resizeObserver.observe(chartElement);

                // Fetch initial chart data
                this.loadData();
            },

            setChartType(type) {
                this.chartType = type;
                this.loadData();
            },

            changeRange(range) {
                this.range = range;
                this.loadData();
            },

            loadData() {
                this.loading = true;
                
                const chartElement = document.getElementById('chart-container');
                const chart = chartElement?.__chart;
                if (!chart) return;

                // Remove existing series
                if (chartElement.__series) {
                    chart.removeSeries(chartElement.__series);
                    chartElement.__series = null;
                }

                // Add correct series type
                let series;
                if (this.chartType === 'area') {
                    series = chart.addAreaSeries({
                        lineColor: '#6366f1',
                        topColor: 'rgba(99, 102, 241, 0.45)',
                        bottomColor: 'rgba(99, 102, 241, 0.02)',
                        lineWidth: 3.5,
                        priceFormat: { type: 'price', precision: 2, minMove: 0.01 }
                    });
                } else {
                    series = chart.addCandlestickSeries({
                        upColor: '#22c55e',
                        downColor: '#ef4444',
                        borderDownColor: '#ef4444',
                        borderUpColor: '#22c55e',
                        wickDownColor: '#ef4444',
                        wickUpColor: '#22c55e',
                        priceFormat: { type: 'price', precision: 2, minMove: 0.01 }
                    });
                }
                chartElement.__series = series;

                fetch(`/api/asset/${this.symbol}/chart?range=${this.range}`)
                    .then(res => res.json())
                    .then(data => {
                        this.loading = false;
                        if (!data.candles || data.candles.length === 0) return;

                        const formattedData = data.candles.map(c => {
                            // Convert epoch timestamp to appropriate format (seconds or date string)
                            // 1d has minutes data (needs seconds timestamp), multi-day has date strings
                            return {
                                time: c.time,
                                open: c.open,
                                high: c.high,
                                low: c.low,
                                close: c.close,
                                value: c.close
                            };
                        });


                        // Sort chronologically
                        formattedData.sort((a, b) => a.time - b.time);

                        series.setData(formattedData);
                        chart.timeScale().fitContent();
                    })
                    .catch(err => {
                        console.error(err);
                        this.loading = false;
                    });
            }
        }
    }
</script>
@endsection
