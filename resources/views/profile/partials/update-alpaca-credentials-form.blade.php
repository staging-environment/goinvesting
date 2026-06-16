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
                <strong>Diferentes llaves:</strong> Alpaca utiliza llaves totalmente distintas para el modo de <strong class="text-indigo-400">Simulación (Paper)</strong> y la cuenta de dinero <strong class="text-emerald-400">Real (Live)</strong>. Debes introducirlas en su apartado correspondiente.
            </li>
            <li>
                <strong>Obtén tus llaves:</strong> Accede a tu panel de control de Alpaca.markets. A la derecha de tu dashboard tendrás las <strong>"API Keys"</strong>. Asegúrate de alternar en el menú de Alpaca entre "Paper" o "Live" antes de generar y copiar la llave.
            </li>
            <li>
                <strong>Cuidado con el auto-relleno:</strong> Por seguridad, mantén los Account ID vacíos a menos que uses una cuenta multidistribuidor (Broker). Tu navegador puede intentar auto-rellenarlos con tu correo.
            </li>
        </ul>
    </div>

    <form method="post" action="{{ route('profile.update-alpaca') }}" class="mt-6 space-y-8" x-data="{ activeMode: '{{ $user->alpaca_is_paper ? 'paper' : 'live' }}' }">
        @csrf

        <!-- Mode Indicator / selector -->
        <div class="flex flex-wrap items-center gap-3 bg-slate-950/40 p-3.5 rounded-2xl border border-slate-900/80">
            <span class="text-xs font-bold text-slate-450">Modo de Conexión Activo:</span>
            <div class="inline-flex p-1 rounded-xl bg-slate-950/80 border border-slate-900/60 shadow-inner">
                <button type="button" @click="activeMode = 'paper'" class="px-4 py-1.5 rounded-lg text-xs font-extrabold transition flex items-center gap-1.5 cursor-pointer" :class="activeMode === 'paper' ? 'bg-indigo-600 text-white shadow-md' : 'text-slate-400 hover:text-slate-200'">
                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-300" :class="activeMode === 'paper' ? 'animate-pulse' : 'opacity-40'"></span>
                    Simulación (Paper)
                </button>
                <button type="button" @click="activeMode = 'live'" class="px-4 py-1.5 rounded-lg text-xs font-extrabold transition flex items-center gap-1.5 cursor-pointer" :class="activeMode === 'live' ? 'bg-emerald-600 text-white shadow-md' : 'text-slate-400 hover:text-slate-200'">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-300" :class="activeMode === 'live' ? 'animate-pulse' : 'opacity-40'"></span>
                    Real (Live)
                </button>
            </div>
            <input type="hidden" name="alpaca_is_paper" :value="activeMode === 'paper' ? '1' : '0'">
            <span class="text-[11px] text-slate-500 font-medium">Este modo define qué conjunto de credenciales utilizará la plataforma y el Bot de Trading por defecto.</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-2">
            <!-- Col 1: Simulation (Paper) -->
            <div class="space-y-4 p-5.5 rounded-2xl bg-indigo-950/5 border transition duration-150 relative" :class="activeMode === 'paper' ? 'border-indigo-500/20 shadow-md shadow-indigo-500/2' : 'border-slate-900/80 opacity-55 hover:opacity-80'">
                <div class="absolute top-4 right-4 flex items-center gap-1 text-[9px] font-extrabold uppercase px-2 py-0.5 rounded border" :class="activeMode === 'paper' ? 'bg-indigo-500/10 text-indigo-400 border-indigo-500/20' : 'bg-slate-900 text-slate-500 border-slate-800'">
                    <span class="w-1.5 h-1.5 rounded-full" :class="activeMode === 'paper' ? 'bg-indigo-400 animate-pulse' : 'bg-slate-500'"></span>
                    Simulación
                </div>
                
                <h3 class="text-sm font-bold text-slate-250 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 text-indigo-400">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v17.792M4.5 12h15" />
                    </svg>
                    Credenciales Simulación (Paper)
                </h3>
                
                <div>
                    <x-input-label for="alpaca_key_id" :value="__('Alpaca Key ID (Paper)')" />
                    <x-text-input id="alpaca_key_id" name="alpaca_key_id" type="text" class="mt-1 block w-full" :value="old('alpaca_key_id', $user->alpaca_key_id)" placeholder="Ingresa Key ID de simulación (PK...)" autocomplete="off" />
                    <x-input-error class="mt-2" :messages="$errors->get('alpaca_key_id')" />
                </div>

                <div>
                    <x-input-label for="alpaca_secret_key" :value="__('Alpaca Secret Key (Paper)')" />
                    <x-text-input id="alpaca_secret_key" name="alpaca_secret_key" type="password" class="mt-1 block w-full" placeholder="••••••••••••••••••••••••••••••••" autocomplete="off" />
                    <span class="text-[10px] text-slate-500 mt-1 block">Por seguridad, la clave secreta se cifra. Deja en blanco si no deseas cambiarla.</span>
                    <x-input-error class="mt-2" :messages="$errors->get('alpaca_secret_key')" />
                </div>

                <div x-data="{ showAdvanced: {{ $user->alpaca_account_id ? 'true' : 'false' }} }" class="space-y-3 pt-2">
                    <button type="button" @click="showAdvanced = !showAdvanced" class="text-[10px] text-indigo-400 hover:text-indigo-300 font-bold flex items-center gap-1 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3 h-3 transition-transform duration-200" :class="showAdvanced ? 'rotate-90' : ''">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                        </svg>
                        Opciones avanzadas (Account ID)
                    </button>
                    <div x-show="showAdvanced" x-cloak class="pt-1">
                        <x-input-label for="alpaca_account_id" :value="__('Alpaca Account ID (Paper)')" />
                        <x-text-input id="alpaca_account_id" name="alpaca_account_id" type="text" class="mt-1 block w-full" :value="old('alpaca_account_id', $user->alpaca_account_id)" placeholder="Opcional. Deja vacío si usas cuenta normal" autocomplete="new-password" />
                        <x-input-error class="mt-2" :messages="$errors->get('alpaca_account_id')" />
                    </div>
                </div>
            </div>

            <!-- Col 2: Live (Real) -->
            <div class="space-y-4 p-5.5 rounded-2xl bg-emerald-950/5 border transition duration-150 relative" :class="activeMode === 'live' ? 'border-emerald-500/20 shadow-md shadow-emerald-500/2' : 'border-slate-900/80 opacity-55 hover:opacity-80'">
                <div class="absolute top-4 right-4 flex items-center gap-1 text-[9px] font-extrabold uppercase px-2 py-0.5 rounded border" :class="activeMode === 'live' ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : 'bg-slate-900 text-slate-500 border-slate-800'">
                    <span class="w-1.5 h-1.5 rounded-full" :class="activeMode === 'live' ? 'bg-emerald-400 animate-pulse' : 'bg-slate-500'"></span>
                    Real
                </div>

                <h3 class="text-sm font-bold text-slate-250 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 text-emerald-400">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 9.89 11.64 9.8 10.47 8.92c-1.173-.879-1.173-2.303 0-3.182 1.171-.879 3.07-.879 4.242 0l.879.659M12 3v3m0 12v3" />
                    </svg>
                    Credenciales Cuenta Real (Live)
                </h3>
                
                <div>
                    <x-input-label for="alpaca_live_key_id" :value="__('Alpaca Key ID (Real)')" />
                    <x-text-input id="alpaca_live_key_id" name="alpaca_live_key_id" type="text" class="mt-1 block w-full" :value="old('alpaca_live_key_id', $user->alpaca_live_key_id)" placeholder="Ingresa Key ID de cuenta real (AK...)" autocomplete="off" />
                    <x-input-error class="mt-2" :messages="$errors->get('alpaca_live_key_id')" />
                </div>

                <div>
                    <x-input-label for="alpaca_live_secret_key" :value="__('Alpaca Secret Key (Real)')" />
                    <x-text-input id="alpaca_live_secret_key" name="alpaca_live_secret_key" type="password" class="mt-1 block w-full" placeholder="••••••••••••••••••••••••••••••••" autocomplete="off" />
                    <span class="text-[10px] text-slate-500 mt-1 block">Por seguridad, la clave secreta se cifra. Deja en blanco si no deseas cambiarla.</span>
                    <x-input-error class="mt-2" :messages="$errors->get('alpaca_live_secret_key')" />
                </div>

                <div x-data="{ showAdvancedLive: {{ $user->alpaca_live_account_id ? 'true' : 'false' }} }" class="space-y-3 pt-2">
                    <button type="button" @click="showAdvancedLive = !showAdvancedLive" class="text-[10px] text-emerald-400 hover:text-emerald-350 font-bold flex items-center gap-1 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3 h-3 transition-transform duration-200" :class="showAdvancedLive ? 'rotate-90' : ''">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                        </svg>
                        Opciones avanzadas (Account ID)
                    </button>
                    <div x-show="showAdvancedLive" x-cloak class="pt-1">
                        <x-input-label for="alpaca_live_account_id" :value="__('Alpaca Account ID (Real)')" />
                        <x-text-input id="alpaca_live_account_id" name="alpaca_live_account_id" type="text" class="mt-1 block w-full" :value="old('alpaca_live_account_id', $user->alpaca_live_account_id)" placeholder="Opcional. Deja vacío si usas cuenta normal" autocomplete="new-password" />
                        <x-input-error class="mt-2" :messages="$errors->get('alpaca_live_account_id')" />
                    </div>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Guardar Credenciales') }}</x-primary-button>

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
                    {{ __('¡Conexión establecida correctamente para el modo activo!') }}
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
