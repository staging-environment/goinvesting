<section>
    <header>
        <h2 class="text-lg font-medium text-slate-100">
            {{ __('Configuración de la Estrategia del Bot') }}
        </h2>

        <p class="mt-1 text-sm text-slate-400">
            {{ __('Define los límites financieros y los umbrales de decisión para tu bot de trading automatizado.') }}
        </p>
    </header>

    <form method="post" action="{{ route('profile.update-bot-strategy') }}" class="mt-6 space-y-6">
        @csrf

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

            <div class="sm:col-span-2">
                <x-input-label for="monthly_spend_limit" :value="__('Límite de Gasto Mensual ($)')" />
                <x-text-input id="monthly_spend_limit" name="monthly_spend_limit" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="old('monthly_spend_limit', $user->monthly_spend_limit)" />
                <span class="text-[10px] text-slate-500 mt-1 block">Presupuesto máximo de compra acumulado en un mes. Deja en blanco para sin límite.</span>
                <x-input-error class="mt-2" :messages="$errors->get('monthly_spend_limit')" />
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
