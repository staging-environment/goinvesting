@extends('layouts.layout')

@section('title', 'Asistente de Bienvenida | GoInvesting')

@section('content')
<div class="max-w-3xl mx-auto py-4" x-data="{ step: 1 }">
    
    <!-- Wizard Header Progress -->
    <div class="mb-8">
        <div class="flex items-center justify-between text-xs text-slate-400 font-bold uppercase tracking-wider mb-3">
            <span>Paso <span x-text="step"></span> de 4</span>
            <span x-show="step === 1">Introducción</span>
            <span x-show="step === 2">Diversificación</span>
            <span x-show="step === 3">Primera Compra</span>
            <span x-show="step === 4">Finalizar</span>
        </div>
        <div class="w-full h-1.5 bg-slate-950/70 border border-slate-900 rounded-full overflow-hidden">
            <div class="h-full bg-gradient-to-r from-indigo-600 to-violet-500 transition-all duration-500 ease-out" 
                 :style="'width: ' + (step * 25) + '%'"></div>
        </div>
    </div>

    <!-- Error/Success Alerts -->
    @if(session('success'))
        <div class="glass-panel border-green-500/20 bg-green-500/5 rounded-xl p-4 text-xs text-green-400 font-medium mb-6">
            {{ session('success') }}
        </div>
    @endif
    @if($errors->any())
        <div class="glass-panel border-red-500/20 bg-red-500/5 rounded-xl p-4 text-xs text-red-400 font-medium mb-6">
            {{ $errors->first() }}
        </div>
    @endif

    <!-- Wizard Card -->
    <div class="glass-panel rounded-2xl p-8 shadow-2xl relative overflow-hidden border border-slate-800 bg-gradient-to-tr from-slate-900/60 to-indigo-950/15">
        <div class="absolute right-0 top-0 w-64 h-64 bg-indigo-500/5 rounded-full blur-3xl pointer-events-none"></div>

        <!-- Step 1: Bienvenida -->
        <div x-show="step === 1" x-transition.opacity.duration.400ms class="space-y-6">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-xl bg-indigo-600/10 border border-indigo-500/30 flex items-center justify-center text-indigo-400">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-xl lg:text-2xl font-extrabold text-white tracking-tight">Conexión con Alpaca Exitosa 🎉</h2>
                    <p class="text-xs text-slate-400 font-semibold uppercase tracking-wider">¡Todo listo para empezar!</p>
                </div>
            </div>

            <p class="text-sm text-slate-300 leading-relaxed">
                ¡Felicidades! Hemos verificado tus credenciales de Alpaca y la integración funciona a la perfección. Tu saldo actual y las futuras compras se sincronizarán directamente.
            </p>

            <div class="p-4 rounded-xl bg-slate-950/60 border border-slate-800 space-y-2">
                <div class="flex items-center justify-between text-xs">
                    <span class="text-slate-400 font-medium">Saldo de Cartera:</span>
                    <span class="text-white font-bold">${{ number_format($account['portfolio_value'], 2) }}</span>
                </div>
                <div class="flex items-center justify-between text-xs">
                    <span class="text-slate-400 font-medium">Efectivo Disponible:</span>
                    <span class="text-white font-bold">${{ number_format($account['cash'], 2) }}</span>
                </div>
            </div>

            <p class="text-sm text-slate-350 leading-relaxed">
                Antes de acceder al portafolio principal, te guiaremos brevemente sobre cómo realizar tu primera compra inteligente.
            </p>

            <div class="flex justify-end pt-4">
                <button @click="step = 2" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold rounded-xl transition duration-150 flex items-center gap-1.5 shadow-lg shadow-indigo-650/20">
                    Siguiente
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3.5 h-3.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Step 2: Diversificación -->
        <div x-show="step === 2" x-transition.opacity.duration.400ms class="space-y-6">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-xl bg-indigo-600/10 border border-indigo-500/30 flex items-center justify-center text-indigo-400">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6a7.5 7.5 0 1 0 7.5 7.5h-7.5V6Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5H21A7.5 7.5 0 0 0 13.5 3v7.5Z" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-xl lg:text-2xl font-extrabold text-white tracking-tight">¿Por qué diversificar? 📊</h2>
                    <p class="text-xs text-slate-400 font-semibold uppercase tracking-wider">Fundamentos de Inversión</p>
                </div>
            </div>

            <div class="space-y-4 text-sm text-slate-300 leading-relaxed">
                <p>
                    La regla de oro de la inversión es: <strong>nunca pongas todos los huevos en la misma cesta</strong>. Comprar diferentes tipos de activos reduce enormemente el riesgo si uno de ellos sufre una bajada temporal.
                </p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                    <div class="p-4 rounded-xl bg-slate-950/45 border border-slate-800/80 space-y-1">
                        <strong class="text-indigo-400 text-xs uppercase tracking-wider block">Activos Estables ("Blue Chips")</strong>
                        <p class="text-xs text-slate-400">Empresas gigantes líderes como Apple o Microsoft, que ofrecen solidez a largo plazo.</p>
                    </div>
                    <div class="p-4 rounded-xl bg-slate-950/45 border border-slate-800/80 space-y-1">
                        <strong class="text-indigo-400 text-xs uppercase tracking-wider block">Crecimiento / Volátiles</strong>
                        <p class="text-xs text-slate-400">Criptomonedas como Bitcoin o empresas tecnológicas en auge que tienen mayor potencial de ganancia y riesgo.</p>
                    </div>
                </div>
            </div>

            <div class="flex justify-between pt-4">
                <button @click="step = 1" class="px-5 py-2.5 bg-slate-950/60 border border-slate-800 text-slate-350 hover:text-white text-xs font-bold rounded-xl transition">
                    Atrás
                </button>
                <button @click="step = 3" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold rounded-xl transition duration-150 flex items-center gap-1.5">
                    Siguiente
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3.5 h-3.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Step 3: Primera Compra Sugerida -->
        <div x-show="step === 3" x-transition.opacity.duration.400ms class="space-y-6">
            <div>
                <h2 class="text-xl lg:text-2xl font-extrabold text-white tracking-tight">Tu Primera Compra Sugerida 💡</h2>
                <p class="text-xs text-slate-400 font-semibold uppercase tracking-wider">Te recomendamos comenzar con una fracción de estas empresas sólidas</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @foreach($recommendedAssets as $asset)
                    @php
                        $isPositive = $asset['changePercent'] >= 0;
                        $colorClass = $isPositive ? 'text-green-400' : 'text-red-400';
                    @endphp
                    <div class="p-5 rounded-2xl bg-slate-950/80 border border-slate-800 flex flex-col justify-between h-56 group/card hover:border-indigo-500/40 transition">
                        <div>
                            <div class="flex justify-between items-start mb-2">
                                <h3 class="font-extrabold text-white text-sm">{{ $asset['name'] }}</h3>
                                <span class="text-[9px] px-2 py-0.5 rounded bg-slate-900 text-slate-400 border border-slate-800 font-bold uppercase">{{ $asset['symbol'] }}</span>
                            </div>
                            <div class="text-xl font-bold text-slate-100">${{ number_format($asset['price'], 2) }}</div>
                            <div class="text-xs {{ $colorClass }} font-bold mb-4">
                                {{ $isPositive ? '▲' : '▼' }} {{ number_format(abs($asset['changePercent']), 2) }}%
                            </div>
                        </div>

                        <!-- Quick Buy Form -->
                        <form method="POST" action="{{ route('trade.execute') }}" class="space-y-2.5">
                            @csrf
                            <input type="hidden" name="symbol" value="{{ $asset['symbol'] }}">
                            <input type="hidden" name="side" value="buy">
                            <input type="hidden" name="type" value="market">
                            
                            <div class="flex items-center gap-1">
                                <label class="text-[9px] text-slate-500 font-bold uppercase">Cant:</label>
                                <input type="number" name="qty" value="1" min="0.0001" step="any" class="w-full bg-slate-900 border border-slate-800 rounded-lg py-1 px-2 text-xs text-slate-200 focus:outline-none focus:border-indigo-500">
                            </div>
                            <button type="submit" class="w-full py-1.5 bg-indigo-600 hover:bg-indigo-500 text-white text-[10px] font-bold rounded-lg transition uppercase tracking-wider">
                                Comprar
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>

            <p class="text-xs text-slate-400 italic">
                * Las compras se ejecutarán de forma inmediata en el mercado a precio de mercado. Si prefieres no comprar nada en este momento, puedes continuar al siguiente paso sin realizar ninguna transacción.
            </p>

            <div class="flex justify-between pt-4 border-t border-slate-900">
                <button @click="step = 2" class="px-5 py-2.5 bg-slate-950/60 border border-slate-800 text-slate-350 hover:text-white text-xs font-bold rounded-xl transition">
                    Atrás
                </button>
                <button @click="step = 4" class="px-5 py-2.5 bg-indigo-650 hover:bg-indigo-550 text-white text-xs font-bold rounded-xl transition flex items-center gap-1.5">
                    Omitir / Siguiente
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3.5 h-3.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Step 4: Finalizar -->
        <div x-show="step === 4" x-transition.opacity.duration.400ms class="space-y-6 text-center py-4">
            <div class="w-16 h-16 rounded-full bg-indigo-600/10 border border-indigo-500/20 flex items-center justify-center text-indigo-400 mx-auto mb-4 animate-bounce">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-8 h-8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z" />
                </svg>
            </div>

            <h2 class="text-xl lg:text-2xl font-extrabold text-white tracking-tight">¡Todo configurado y listo! 🎉</h2>
            <p class="text-sm text-slate-350 max-w-md mx-auto leading-relaxed">
                Has completado la guía inicial. A partir de ahora, puedes configurar tu estrategia personalizada del bot en tu perfil para que realice operaciones automáticamente.
            </p>

            <form method="POST" action="{{ route('portfolio.complete-wizard') }}" class="pt-6">
                @csrf
                <div class="flex justify-center gap-4">
                    <button type="button" @click="step = 3" class="px-5 py-2.5 bg-slate-950/60 border border-slate-800 text-slate-300 hover:text-white text-xs font-bold rounded-xl transition">
                        Atrás
                    </button>
                    <button type="submit" class="px-6 py-3 bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white text-xs font-extrabold rounded-xl shadow-lg shadow-indigo-650/20 transition duration-150 uppercase tracking-wider">
                        Completar y Ver Portafolio
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>
@endsection
