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

<script>
    function chartManager() {
        return {
            symbol: '{{ $assetData['symbol'] }}',
            range: '1d',
            chartType: 'area', // or candlestick
            loading: true,
            chart: null,
            mainSeries: null,

            init() {
                // Initialize TradingView Chart on mount
                const chartElement = document.getElementById('chart-container');
                
                this.chart = LightweightCharts.createChart(chartElement, {
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

                // Handle window resizing
                const resizeObserver = new ResizeObserver(entries => {
                    if (entries.length === 0 || !entries[0].contentRect) return;
                    const { width, height } = entries[0].contentRect;
                    this.chart.resize(width, height);
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
                
                // Remove existing series
                if (this.mainSeries) {
                    this.chart.removeSeries(this.mainSeries);
                    this.mainSeries = null;
                }

                // Add correct series type
                if (this.chartType === 'area') {
                    this.mainSeries = this.chart.addAreaSeries({
                        lineColor: '#6366f1',
                        topColor: 'rgba(99, 102, 241, 0.45)',
                        bottomColor: 'rgba(99, 102, 241, 0.02)',
                        lineWidth: 3.5,
                        priceFormat: { type: 'price', precision: 2, minMove: 0.01 }
                    });
                } else {
                    this.mainSeries = this.chart.addCandlestickSeries({
                        upColor: '#22c55e',
                        downColor: '#ef4444',
                        borderDownColor: '#ef4444',
                        borderUpColor: '#22c55e',
                        wickDownColor: '#ef4444',
                        wickUpColor: '#22c55e',
                        priceFormat: { type: 'price', precision: 2, minMove: 0.01 }
                    });
                }

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
                                close: c.close
                            };
                        });

                        // Sort chronologically
                        formattedData.sort((a, b) => a.time - b.time);

                        this.mainSeries.setData(formattedData);
                        this.chart.timeScale().fitContent();
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
