<x-guest-layout>
    <div x-data="{ showTermsModal: false, termsRead: false, termsAccepted: false }">
        <form method="POST" action="{{ route('register') }}">
            @csrf

            <!-- Name -->
            <div>
                <x-input-label for="name" :value="__('Nombre')" />
                <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <!-- Email Address -->
            <div class="mt-4">
                <x-input-label for="email" :value="__('Correo Electrónico')" />
                <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <!-- Password -->
            <div class="mt-4">
                <x-input-label for="password" :value="__('Contraseña')" />

                <x-text-input id="password" class="block mt-1 w-full"
                                type="password"
                                name="password"
                                required autocomplete="new-password" />

                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <!-- Confirm Password -->
            <div class="mt-4">
                <x-input-label for="password_confirmation" :value="__('Confirmar Contraseña')" />

                <x-text-input id="password_confirmation" class="block mt-1 w-full"
                                type="password"
                                name="password_confirmation" required autocomplete="new-password" />

                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
            </div>

            <!-- Mathematical Captcha -->
            <div class="mt-4">
                <x-input-label for="captcha" :value="__('Seguridad: ¿Cuánto es ' . $num1 . ' + ' . $num2 . '?')" />

                <div class="relative mt-1">
                    <x-text-input id="captcha" class="block w-full pr-10"
                                   type="text"
                                   name="captcha"
                                   required
                                   placeholder="Escribe el resultado numérico" />
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-slate-500">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 text-indigo-400">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                        </svg>
                    </div>
                </div>

                <x-input-error :messages="$errors->get('captcha')" class="mt-2" />
            </div>

            <!-- Legal Terms Checkbox -->
            <div class="mt-4">
                <label class="inline-flex items-start gap-3 cursor-pointer">
                    <input type="checkbox" 
                           name="terms" 
                           id="terms" 
                           class="mt-0.5 rounded border-slate-800 bg-slate-950 text-indigo-600 focus:ring-indigo-500 focus:ring-offset-slate-900 focus:ring-offset-2 transition cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed" 
                           :disabled="!termsRead" 
                           x-model="termsAccepted" 
                           required>
                    <span class="text-xs text-slate-400 font-medium leading-tight select-none">
                        He leído y acepto de forma obligatoria la 
                        <button type="button" @click="showTermsModal = true" class="text-indigo-400 font-bold hover:text-indigo-300 underline inline cursor-pointer">
                            Declaración de Riesgo y Términos de Servicio
                        </button>
                    </span>
                </label>
                <div x-show="!termsRead" class="text-[10px] text-amber-500 font-bold mt-1.5 flex items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3.5 h-3.5 shrink-0">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                    </svg>
                    <span>Debes abrir y aceptar los términos de riesgo para activar la casilla.</span>
                </div>
                <x-input-error :messages="$errors->get('terms')" class="mt-2" />
            </div>

            <div class="flex items-center justify-end mt-6">
                <a class="underline text-sm text-slate-400 hover:text-slate-200 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}">
                    {{ __('¿Ya estás registrado?') }}
                </a>

                <x-primary-button class="ms-4">
                    {{ __('Registrarse') }}
                </x-primary-button>
            </div>
        </form>

        <!-- Floating Legal Modal -->
        <div x-show="showTermsModal" 
             x-cloak 
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/85 backdrop-blur-md"
             x-transition>
            
            <div class="glass-panel w-full max-w-lg rounded-3xl p-6 shadow-2xl border border-slate-800 bg-[#0d1222]/95 space-y-6 relative overflow-hidden" 
                 @click.outside="showTermsModal = false">
                
                <div class="absolute right-4 top-4">
                    <button type="button" @click="showTermsModal = false" class="text-slate-400 hover:text-white transition cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-400 shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                        </svg>
                    </div>
                    <div class="text-left">
                        <h3 class="text-base font-extrabold text-white">Declaración de Riesgo y Términos</h3>
                        <p class="text-xs text-slate-400">Por favor, lea atentamente antes de registrarse en GoInvesting</p>
                    </div>
                </div>

                <!-- Legal Scrollable Text Area -->
                <div class="max-h-60 overflow-y-auto pr-2 text-xs text-slate-350 space-y-3.5 leading-relaxed text-left border border-slate-900 bg-slate-950/40 p-4 rounded-xl">
                    <p class="font-bold text-white uppercase text-center border-b border-slate-900/60 pb-2">
                        ADVERTENCIA DE RIESGO Y EXCLUSIÓN DE RESPONSABILIDAD
                    </p>
                    <p>
                        <strong>1. Riesgo de Pérdida Financiera:</strong> El usuario declara conocer y aceptar que operar en los mercados financieros globales (incluyendo acciones, divisas, criptomonedas y materias primas) a través de herramientas algorítmicas, automatizadas o manuales implica un riesgo extremadamente elevado. Existe la posibilidad real de perder una parte o la totalidad del capital invertido.
                    </p>
                    <p>
                        <strong>2. Ausencia de Garantías:</strong> GoInvesting no garantiza beneficios, rentabilidades ni el éxito de ninguna estrategia de inversión configurada o ejecutada por el bot. Los rendimientos históricos o simulados no garantizan resultados futuros.
                    </p>
                    <p>
                        <strong>3. Responsabilidad del Usuario:</strong> El uso de esta plataforma, la conexión de las credenciales de la API de Alpaca (tanto en modo simulación/paper como en modo real/live) y las decisiones de compra o venta automáticas o manuales se realizan bajo la entera y exclusiva responsabilidad del usuario.
                    </p>
                    <p>
                        <strong>4. Exclusión de Responsabilidad por Fallos Técnicos:</strong> GoInvesting declina cualquier responsabilidad por pérdidas derivadas de fallos de red, retrasos en la API del broker, inactividad del mercado, errores en el algoritmo de ejecución, o cualquier interrupción técnica ajena a nuestro control directo.
                    </p>
                </div>

                <div class="pt-2">
                    <button type="button" 
                            @click="termsRead = true; termsAccepted = true; showTermsModal = false" 
                            class="w-full inline-flex justify-center items-center gap-1.5 bg-indigo-600 hover:bg-indigo-500 text-white font-extrabold text-xs py-3 px-4 rounded-xl transition shadow-md shadow-indigo-600/10 cursor-pointer">
                        He leído y acepto los riesgos y condiciones
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 text-indigo-200">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1 3.296-1.043A3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>
