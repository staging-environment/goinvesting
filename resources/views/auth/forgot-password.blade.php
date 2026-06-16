<x-guest-layout>
    <div class="mb-4 text-sm text-slate-400">
        {{ __('¿Olvidaste tu contraseña? No hay problema. Simplemente haznos saber tu dirección de correo electrónico y te enviaremos por correo un enlace de restablecimiento de contraseña que te permitirá elegir una nueva.') }}
    </div>

    <!-- Estado de la Sesión -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Correo Electrónico')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-6">
            <x-primary-button>
                {{ __('Enviar Enlace de Restablecimiento') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
