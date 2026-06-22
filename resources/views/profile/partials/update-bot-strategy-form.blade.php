<section>
    <header>
        <h2 class="text-lg font-medium text-slate-100">
            {{ __('Configuración de la Estrategia del Bot') }}
        </h2>

        <p class="mt-1 text-sm text-slate-400">
            {{ __('Define los límites financieros y los umbrales de decisión para tu bot de trading automatizado.') }}
        </p>
    </header>

    <form method="post" action="{{ route('profile.update-bot-strategy') }}" class="mt-6 space-y-8">
        @csrf

        <div class="space-y-8">
            <!-- PANEL 1: SIMULACIÓN -->
            <div class="p-6 bg-slate-900/20 border border-slate-800/80 rounded-2xl space-y-4">
                <h3 class="text-sm font-extrabold text-indigo-400 uppercase tracking-wider border-b border-slate-800/80 pb-2.5 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Parámetros de Simulación (Demo)
                </h3>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <x-input-label for="bot_buy_threshold" :value="__('Umbral de Compra (Caída Diaria %)')" />
                        <x-text-input id="bot_buy_threshold" name="bot_buy_threshold" type="number" step="0.01" class="mt-1 block w-full" :value="old('bot_buy_threshold', $user->bot_buy_threshold ?? -1.5)" required />
                        <span class="text-[10px] text-slate-500 mt-1 block">El bot comprará cuando el cambio diario del activo caiga por debajo de este porcentaje (ej: -1.5%).</span>
                        <x-input-error class="mt-2" :messages="$errors->get('bot_buy_threshold')" />
                    </div>

                    <div>
                        <x-input-label for="bot_take_profit" :value="__('Margen de Ganancia (Take Profit %)')" />
                        <x-text-input id="bot_take_profit" name="bot_take_profit" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="old('bot_take_profit', $user->bot_take_profit ?? 2.0)" required />
                        <span class="text-[10px] text-slate-500 mt-1 block">El bot venderá la posición cuando alcance este porcentaje de ganancia acumulada (ej: 2.0%).</span>
                        <x-input-error class="mt-2" :messages="$errors->get('bot_take_profit')" />
                    </div>

                    <div>
                        <x-input-label for="bot_stop_loss" :value="__('Margen de Pérdida (Stop Loss %)')" />
                        <x-text-input id="bot_stop_loss" name="bot_stop_loss" type="number" step="0.01" max="0" class="mt-1 block w-full" :value="old('bot_stop_loss', $user->bot_stop_loss ?? -3.0)" required />
                        <span class="text-[10px] text-slate-500 mt-1 block">El bot venderá para detener pérdidas si el rendimiento cae por debajo de este porcentaje negativo (ej: -3.0%).</span>
                        <x-input-error class="mt-2" :messages="$errors->get('bot_stop_loss')" />
                    </div>

                    <div>
                        <x-input-label for="bot_order_size" :value="__('Tamaño de Orden de Compra ($)')" />
                        <x-text-input id="bot_order_size" name="bot_order_size" type="number" step="0.01" min="1" class="mt-1 block w-full" :value="old('bot_order_size', $user->bot_order_size ?? 500.0)" required />
                        <span class="text-[10px] text-slate-500 mt-1 block">La cantidad de dinero destinado para comprar acciones de un activo en cada operación (ej: $500).</span>
                        <x-input-error class="mt-2" :messages="$errors->get('bot_order_size')" />
                    </div>

                    <div>
                        <x-input-label for="bot_max_investment" :value="__('Límite Máximo de Inversión del Bot ($)')" />
                        <x-text-input id="bot_max_investment" name="bot_max_investment" type="number" step="0.01" min="1" class="mt-1 block w-full" :value="old('bot_max_investment', $user->bot_max_investment ?? 500000.0)" required />
                        <span class="text-[10px] text-slate-500 mt-1 block">El presupuesto total máximo de tu cartera que el bot puede tener invertido en total de manera simultánea (ej: $500,000).</span>
                        <x-input-error class="mt-2" :messages="$errors->get('bot_max_investment')" />
                    </div>

                    <div>
                        <x-input-label for="daily_spend_limit" :value="__('Límite de Gasto Diario ($)')" />
                        <x-text-input id="daily_spend_limit" name="daily_spend_limit" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="old('daily_spend_limit', $user->daily_spend_limit)" />
                        <span class="text-[10px] text-slate-500 mt-1 block">Presupuesto máximo de compra que el bot puede realizar en un solo día. Deja en blanco para sin límite.</span>
                        <x-input-error class="mt-2" :messages="$errors->get('daily_spend_limit')" />
                    </div>

                    <div>
                        <x-input-label for="weekly_spend_limit" :value="__('Límite de Gasto Semanal ($)')" />
                        <x-text-input id="weekly_spend_limit" name="weekly_spend_limit" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="old('weekly_spend_limit', $user->weekly_spend_limit)" />
                        <span class="text-[10px] text-slate-500 mt-1 block">Presupuesto máximo de compra acumulado en una semana. Deja en blanco para sin límite.</span>
                        <x-input-error class="mt-2" :messages="$errors->get('weekly_spend_limit')" />
                    </div>

                    <div>
                        <x-input-label for="monthly_spend_limit" :value="__('Límite de Gasto Mensual ($)')" />
                        <x-text-input id="monthly_spend_limit" name="monthly_spend_limit" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="old('monthly_spend_limit', $user->monthly_spend_limit)" />
                        <span class="text-[10px] text-slate-500 mt-1 block">Presupuesto máximo de compra acumulado en un mes. Deja en blanco para sin límite.</span>
                        <x-input-error class="mt-2" :messages="$errors->get('monthly_spend_limit')" />
                    </div>
                </div>
            </div>

            <!-- PANEL 2: REAL -->
            <div class="p-6 bg-emerald-950/5 border border-emerald-500/10 rounded-2xl space-y-4">
                <h3 class="text-sm font-extrabold text-emerald-400 uppercase tracking-wider border-b border-emerald-500/10 pb-2.5 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.879c1.46.177 2.122-.177 2.122-1.005 0-1.112-.879-1.217-2.122-1.286-1.19-.035-2.125-.136-2.125-1.378 0-1.144.902-1.353 2.125-1.387m.879-.879V6M9 14.182c0-.188.016-.368.046-.543M15 11.182c0 .188-.016.368-.046.543M12 18V6" />
                    </svg>
                    Parámetros de Dinero Real (Live)
                </h3>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <x-input-label for="live_bot_buy_threshold" :value="__('Umbral de Compra (Caída Diaria %)')" />
                        <x-text-input id="live_bot_buy_threshold" name="live_bot_buy_threshold" type="number" step="0.01" class="mt-1 block w-full border-emerald-500/20 focus:border-emerald-500 focus:ring-emerald-500" :value="old('live_bot_buy_threshold', $user->live_bot_buy_threshold ?? -1.5)" required />
                        <span class="text-[10px] text-slate-500 mt-1 block">El bot comprará cuando el cambio diario del activo caiga por debajo de este porcentaje (ej: -1.5%).</span>
                        <x-input-error class="mt-2" :messages="$errors->get('live_bot_buy_threshold')" />
                    </div>

                    <div>
                        <x-input-label for="live_bot_take_profit" :value="__('Margen de Ganancia (Take Profit %)')" />
                        <x-text-input id="live_bot_take_profit" name="live_bot_take_profit" type="number" step="0.01" min="0" class="mt-1 block w-full border-emerald-500/20 focus:border-emerald-500 focus:ring-emerald-500" :value="old('live_bot_take_profit', $user->live_bot_take_profit ?? 2.0)" required />
                        <span class="text-[10px] text-slate-500 mt-1 block">El bot venderá la posición cuando alcance este porcentaje de ganancia acumulada (ej: 2.0%).</span>
                        <x-input-error class="mt-2" :messages="$errors->get('live_bot_take_profit')" />
                    </div>

                    <div>
                        <x-input-label for="live_bot_stop_loss" :value="__('Margen de Pérdida (Stop Loss %)')" />
                        <x-text-input id="live_bot_stop_loss" name="live_bot_stop_loss" type="number" step="0.01" max="0" class="mt-1 block w-full border-emerald-500/20 focus:border-emerald-500 focus:ring-emerald-500" :value="old('live_bot_stop_loss', $user->live_bot_stop_loss ?? -3.0)" required />
                        <span class="text-[10px] text-slate-500 mt-1 block">El bot venderá para detener pérdidas si el rendimiento cae por debajo de este porcentaje negativo (ej: -3.0%).</span>
                        <x-input-error class="mt-2" :messages="$errors->get('live_bot_stop_loss')" />
                    </div>

                    <div>
                        <x-input-label for="live_bot_order_size" :value="__('Tamaño de Orden de Compra ($)')" />
                        <x-text-input id="live_bot_order_size" name="live_bot_order_size" type="number" step="0.01" min="1" class="mt-1 block w-full border-emerald-500/20 focus:border-emerald-500 focus:ring-emerald-500" :value="old('live_bot_order_size', $user->live_bot_order_size ?? 500.0)" required />
                        <span class="text-[10px] text-slate-500 mt-1 block">La cantidad de dinero destinado para comprar acciones de un activo en cada operación (ej: $500).</span>
                        <x-input-error class="mt-2" :messages="$errors->get('live_bot_order_size')" />
                    </div>

                    <div>
                        <x-input-label for="live_bot_max_investment" :value="__('Límite Máximo de Inversión del Bot ($)')" />
                        <x-text-input id="live_bot_max_investment" name="live_bot_max_investment" type="number" step="0.01" min="1" class="mt-1 block w-full border-emerald-500/20 focus:border-emerald-500 focus:ring-emerald-500" :value="old('live_bot_max_investment', $user->live_bot_max_investment ?? 500000.0)" required />
                        <span class="text-[10px] text-slate-500 mt-1 block">El presupuesto total máximo de tu cartera que el bot puede tener invertido en total de manera simultánea (ej: $500,000).</span>
                        <x-input-error class="mt-2" :messages="$errors->get('live_bot_max_investment')" />
                    </div>

                    <div>
                        <x-input-label for="live_daily_spend_limit" :value="__('Límite de Gasto Diario ($)')" />
                        <x-text-input id="live_daily_spend_limit" name="live_daily_spend_limit" type="number" step="0.01" min="0" class="mt-1 block w-full border-emerald-500/20 focus:border-emerald-500 focus:ring-emerald-500" :value="old('live_daily_spend_limit', $user->live_daily_spend_limit)" />
                        <span class="text-[10px] text-slate-500 mt-1 block">Presupuesto máximo de compra que el bot puede realizar en un solo día. Deja en blanco para sin límite.</span>
                        <x-input-error class="mt-2" :messages="$errors->get('live_daily_spend_limit')" />
                    </div>

                    <div>
                        <x-input-label for="live_weekly_spend_limit" :value="__('Límite de Gasto Semanal ($)')" />
                        <x-text-input id="live_weekly_spend_limit" name="live_weekly_spend_limit" type="number" step="0.01" min="0" class="mt-1 block w-full border-emerald-500/20 focus:border-emerald-500 focus:ring-emerald-500" :value="old('live_weekly_spend_limit', $user->live_weekly_spend_limit)" />
                        <span class="text-[10px] text-slate-500 mt-1 block">Presupuesto máximo de compra acumulado en una semana. Deja en blanco para sin límite.</span>
                        <x-input-error class="mt-2" :messages="$errors->get('live_weekly_spend_limit')" />
                    </div>

                    <div>
                        <x-input-label for="live_monthly_spend_limit" :value="__('Límite de Gasto Mensual ($)')" />
                        <x-text-input id="live_monthly_spend_limit" name="live_monthly_spend_limit" type="number" step="0.01" min="0" class="mt-1 block w-full border-emerald-500/20 focus:border-emerald-500 focus:ring-emerald-500" :value="old('live_monthly_spend_limit', $user->live_monthly_spend_limit)" />
                        <span class="text-[10px] text-slate-500 mt-1 block">Presupuesto máximo de compra acumulado en un mes. Deja en blanco para sin límite.</span>
                        <x-input-error class="mt-2" :messages="$errors->get('live_monthly_spend_limit')" />
                    </div>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-4 pt-2">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'bot-strategy-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-400"
                >{{ __('Guardado.') }}</p>
            @endif
        </div>
    </form>
</section>
