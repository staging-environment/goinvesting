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

    @if(isset($error))
        <!-- Error / Config State -->
        <div class="glass-panel rounded-2xl p-8 text-center space-y-4 max-w-xl mx-auto border-red-500/20 bg-red-500/5">
            <div class="w-12 h-12 rounded-full bg-red-500/10 flex items-center justify-center mx-auto text-red-400">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                </svg>
            </div>
            <h2 class="text-lg font-bold text-white">Configuración Pendiente</h2>
            <p class="text-sm text-slate-400 leading-relaxed">
                {{ $error }}
            </p>
            <div class="text-xs text-slate-500 bg-slate-950/40 p-3.5 rounded-xl text-left border border-slate-900 font-mono">
                # Agrega esto a tu .env de producción:<br>
                ALPACA_KEY_ID="tu_key_id"<br>
                ALPACA_SECRET_KEY="tu_secret_key"<br>
                ALPACA_IS_PAPER=true
            </div>
        </div>
    @elseif(!$account)
        <!-- Empty / Failed Connection State -->
        <div class="glass-panel rounded-2xl p-8 text-center space-y-4 max-w-xl mx-auto">
            <div class="w-12 h-12 rounded-full bg-amber-500/10 flex items-center justify-center mx-auto text-amber-400 animate-pulse">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 21v-4.875c0-.621.504-1.125 1.125-1.125h5.25c.621 0 1.125.504 1.125 1.125V21m0 0h4.5V3.545M12.75 21h7.5V10.75M2.25 21h1.5m18 0h-18M2.25 9l4.5-1.636M18.75 3l-1.5.545m0 6.205 3 1m1.5-8.5-3-.75m-9 15.25V7.5L5.25 5.5m0 0 3-1.336M5.25 5.5v10.5" />
                </svg>
            </div>
            <h2 class="text-lg font-bold text-white">Error de Conexión con Alpaca</h2>
            <p class="text-sm text-slate-400">
                No pudimos establecer comunicación con el servidor de Alpaca. Por favor verifica tus credenciales y estado del servidor de Alpaca.
            </p>
        </div>
    @else
        <!-- Account Summary Cards -->
        @php
            $portfolioValue = (float)$account['portfolio_value'];
            $cash = (float)$account['cash'];
            $buyingPower = (float)$account['buying_power'];
            $initialMargin = (float)$account['initial_margin'];
            $isPaper = str_contains($account['currency'], 'USD') && config('services.alpaca.is_paper');
        @endphp
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Net Asset Value -->
            <div class="glass-panel rounded-2xl p-6 shadow-xl space-y-2 relative overflow-hidden bg-gradient-to-tr from-slate-900 to-indigo-950/30">
                <span class="text-xs text-slate-500 font-bold uppercase tracking-wider block">Valor de Cartera (Net Worth)</span>
                <span class="text-3xl font-extrabold text-white block">${{ number_format($portfolioValue, 2) }}</span>
                <div class="flex items-center gap-1.5 mt-2">
                    <span class="text-xs px-2 py-0.5 rounded-md font-extrabold bg-indigo-500/10 text-indigo-400 border border-indigo-500/25">
                        {{ $isPaper ? 'Paper Portfolio' : 'Live Portfolio' }}
                    </span>
                    <span class="text-xs text-slate-500 font-medium">Moneda: {{ $account['currency'] }}</span>
                </div>
            </div>

            <!-- Cash & Capital -->
            <div class="glass-panel rounded-2xl p-6 shadow-xl space-y-2">
                <span class="text-xs text-slate-500 font-bold uppercase tracking-wider block">Efectivo Disponible (Cash)</span>
                <span class="text-3xl font-extrabold text-slate-200 block">${{ number_format($cash, 2) }}</span>
                <span class="text-xs text-slate-500 block">Garantía Inicial: ${{ number_format($initialMargin, 2) }}</span>
            </div>

            <!-- Buying Power -->
            <div class="glass-panel rounded-2xl p-6 shadow-xl space-y-2">
                <span class="text-xs text-slate-500 font-bold uppercase tracking-wider block">Poder de Compra (Buying Power)</span>
                <span class="text-3xl font-extrabold text-indigo-400 block">${{ number_format($buyingPower, 2) }}</span>
                <span class="text-xs text-slate-500 block">Apalancamiento Máx: {{ $account['multiplier'] }}x</span>
            </div>
        </div>

        <!-- Open Positions Table -->
        <div class="space-y-4">
            <h2 class="text-lg font-extrabold text-white flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-indigo-400">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 0 0 6 16.5h2.25M3.75 3h-1.5m1.5 0v11.25m0 0h19.5m0 0h-2.25m-14.25-2.25h14.25m-14.25-3h14.25m-14.25-3H21" />
                </svg>
                Mis Posiciones Abiertas
            </h2>

            <div class="glass-panel rounded-2xl overflow-hidden shadow-xl">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse min-w-[700px]">
                        <thead>
                            <tr class="border-b border-slate-900 text-xs font-bold uppercase tracking-wider text-slate-500 bg-[#070913]/30">
                                <th class="py-4 px-5">Símbolo</th>
                                <th class="py-4 px-5">Nombre</th>
                                <th class="py-4 px-5 text-right">Cantidad</th>
                                <th class="py-4 px-5 text-right">Precio Medio</th>
                                <th class="py-4 px-5 text-right">Precio Actual</th>
                                <th class="py-4 px-5 text-right">Costo Total</th>
                                <th class="py-4 px-5 text-right">Valor actual</th>
                                <th class="py-4 px-5 text-right">G/P no realizado</th>
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
    @endif

</div>
@endsection
