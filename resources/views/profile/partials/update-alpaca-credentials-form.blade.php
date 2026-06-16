<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Credenciales de Alpaca API') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __('Configura tus credenciales personales de Alpaca Broker para interconectar tu cuenta con la plataforma de trading.') }}
        </p>
    </header>

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
            <input id="alpaca_is_paper" name="alpaca_is_paper" type="checkbox" value="1" {{ old('alpaca_is_paper', $user->alpaca_is_paper) ? 'checked' : '' }} class="rounded border-gray-350 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500 focus:ring-offset-gray-800">
            <x-input-label for="alpaca_is_paper" :value="__('Cuenta de Simulación (Paper Trading)')" class="inline" />
            <x-input-error class="mt-2" :messages="$errors->get('alpaca_is_paper')" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'alpaca-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600 dark:text-gray-400"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
