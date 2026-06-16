<section>
    <header>
        <h2 class="text-lg font-medium text-slate-100">
            {{ __('Credenciales de Alpaca API') }}
        </h2>

        <p class="mt-1 text-sm text-slate-400">
            {{ __('Configura tus credenciales personales de Alpaca Broker para interconectar tu cuenta con la plataforma de trading.') }}
        </p>
    </header>

    <div class="mt-4 p-4 bg-indigo-950/20 rounded-2xl border border-indigo-500/10 space-y-3">
        <h3 class="text-xs font-extrabold text-indigo-400 uppercase tracking-wider flex items-center gap-1.5">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 1 1 .513 1.293l-.042.015-1.478.492a1 1 0 0 0-.674.933V15m3.75 2.25h.008v.008H13v-.008Z" />
            </svg>
            Guía de Integración con Alpaca (Paso a Paso)
        </h3>
        <ul class="text-xs text-slate-400 space-y-2 list-decimal pl-4 leading-relaxed font-medium">
            <li>
                <strong>Crea una cuenta:</strong> Regístrate gratis en <a href="https://alpaca.markets/" target="_blank" class="text-indigo-405 hover:text-indigo-300 font-bold underline transition">Alpaca.markets</a> (puedes empezar usando una cuenta de simulación o "Paper Trading" sin dinero real).
            </li>
            <li>
                <strong>Obtén tus credenciales:</strong> Accede a tu panel de control de Alpaca, navega a la sección de <strong>"API Keys"</strong> en la derecha y haz clic en <strong>"Generate Key"</strong>. Copia tu <span class="text-slate-200 font-bold">API Key ID</span> y tu <span class="text-slate-200 font-bold">Secret Key</span>.
            </li>
            <li>
                <strong>Configura el modo:</strong> Si utilizas claves de simulación (Paper), marca la casilla de <em>"Cuenta de Simulación (Paper Trading)"</em> abajo. Si usas tu cuenta real de inversiones, déjala desmarcada.
            </li>
            <li>
                <strong>Origen de Precios vs Ejecución:</strong> Los gráficos y precios de mercados mundiales se muestran a través de <strong class="text-indigo-400">Yahoo Finance</strong> en tiempo real. Sin embargo, para realizar compras, ventas o consultar tu saldo real de cuenta, GoInvesting requiere interactuar con el broker de <strong class="text-indigo-400">Alpaca</strong>. Si no integras estas claves correctamente, la plataforma no podrá colocar órdenes.
            </li>
        </ul>
    </div>

    <form method="post" action="{{ route('profile.update-alpaca') }}" class="mt-6 space-y-6">
        @csrf

        <div>
            <x-input-label for="alpaca_key_id" :value="__('Alpaca Key ID')" />
            <x-text-input id="alpaca_key_id" name="alpaca_key_id" type="text" class="mt-1 block w-full" :value="old('alpaca_key_id', $user->alpaca_key_id)" placeholder="Ingresa tu API Key ID" autocomplete="off" />
            <x-input-error class="mt-2" :messages="$errors->get('alpaca_key_id')" />
        </div>

        <div>
            <x-input-label for="alpaca_secret_key" :value="__('Alpaca Secret Key')" />
            <x-text-input id="alpaca_secret_key" name="alpaca_secret_key" type="password" class="mt-1 block w-full" placeholder="••••••••••••••••••••••••••••••••" autocomplete="off" />
            <span class="text-[11px] text-slate-500 mt-1 block">Por seguridad, tu clave secreta se guarda cifrada y no se muestra aquí después de guardarse. Deja este campo vacío si no deseas cambiarlo.</span>
            <x-input-error class="mt-2" :messages="$errors->get('alpaca_secret_key')" />
        </div>

        <div>
            <x-input-label for="alpaca_account_id" :value="__('Alpaca Account ID (Opcional, para bróker/sandbox)')" />
            <x-text-input id="alpaca_account_id" name="alpaca_account_id" type="text" class="mt-1 block w-full" :value="old('alpaca_account_id', $user->alpaca_account_id)" placeholder="Ingresa tu Account ID si usas cuenta Broker" autocomplete="off" />
            <x-input-error class="mt-2" :messages="$errors->get('alpaca_account_id')" />
        </div>

        <div class="flex items-center gap-2">
            <input id="alpaca_is_paper" name="alpaca_is_paper" type="checkbox" value="1" {{ old('alpaca_is_paper', $user->alpaca_is_paper) ? 'checked' : '' }} class="rounded border-slate-800 bg-slate-950 text-indigo-600 shadow-sm focus:ring-indigo-500 focus:ring-offset-slate-900">
            <x-input-label for="alpaca_is_paper" :value="__('Cuenta de Simulación (Paper Trading)')" class="inline" />
            <x-input-error class="mt-2" :messages="$errors->get('alpaca_is_paper')" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'alpaca-updated-success')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 5000)"
                    class="text-sm text-green-450 font-bold flex items-center gap-1.5"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4 text-green-400">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd" />
                    </svg>
                    {{ __('¡Conexión con Alpaca establecida correctamente!') }}
                </p>
            @endif

            @if (session('status') === 'alpaca-updated-error')
                <div class="p-3 bg-red-500/10 border border-red-500/30 text-red-400 text-xs rounded-xl flex flex-col gap-1 w-full max-w-md">
                    <div class="flex items-center gap-1.5 font-bold">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4 text-red-400">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16ZM8.28 7.22a.75.75 0 0 0-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 1 0 1.06 1.06L10 11.06l1.72 1.72a.75.75 0 1 0 1.06-1.06L11.06 10l1.72-1.72a.75.75 0 0 0-1.06-1.06L10 8.94 8.28 7.22Z" clip-rule="evenodd" />
                        </svg>
                        {{ __('Conexión fallida') }}
                    </div>
                    <p class="text-[11px] leading-normal opacity-90">{{ session('alpaca_error_msg') }}</p>
                </div>
            @endif
        </div>
    </form>
</section>
