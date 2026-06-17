@extends('layouts.layout')

@section('title', 'Cómo Empezar | GoInvesting')

@section('content')
<div class="max-w-4xl mx-auto space-y-10 py-4">
    
    <!-- Hero Header -->
    <div class="text-center space-y-4">
        <div class="inline-flex px-3.5 py-1.5 rounded-full bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 text-xs font-black uppercase tracking-wider select-none">
            🚀 Guía de Inicio Rápido
        </div>
        <h1 class="text-3xl lg:text-4xl font-extrabold text-white tracking-tight leading-tight">
            Cómo empezar a operar en <span class="bg-gradient-to-r from-indigo-400 via-violet-400 to-indigo-300 bg-clip-text text-transparent">GoInvesting</span>
        </h1>
        <p class="text-sm text-slate-400 max-w-2xl mx-auto leading-relaxed">
            Sigue estos sencillos pasos para conectar tu cuenta del broker Alpaca Markets y permitir que nuestro Bot de Trading inteligente comience a operar de forma automatizada por ti.
        </p>
    </div>

    <!-- Steps Vertical Grid -->
    <div class="space-y-6">
        
        <!-- Step 1 -->
        <div class="glass-panel rounded-2xl p-6 lg:p-8 relative overflow-hidden group hover:border-indigo-500/25 transition duration-300">
            <div class="absolute right-0 top-0 w-24 h-24 bg-indigo-500/5 rounded-full blur-2xl group-hover:bg-indigo-500/10 transition duration-300"></div>
            <div class="flex flex-col sm:flex-row gap-5 items-start">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-500 to-indigo-650 flex items-center justify-center font-black text-white shrink-0 shadow-lg shadow-indigo-500/20 text-sm select-none">
                    01
                </div>
                <div class="space-y-3 flex-1">
                    <h3 class="text-lg font-bold text-white tracking-tight">Regístrate en Alpaca Markets</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        GoInvesting se integra directamente con **Alpaca Markets**, un broker estadounidense regulado que ofrece trading automatizado sin comisiones mediante llaves de API.
                    </p>
                    <div class="pt-1">
                        <a href="https://app.alpaca.markets/signup" target="_blank" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-extrabold transition shadow-md shadow-indigo-600/10">
                            Crear cuenta en Alpaca
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3.5 h-3.5 text-indigo-300">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 2 -->
        <div class="glass-panel rounded-2xl p-6 lg:p-8 relative overflow-hidden group hover:border-indigo-500/25 transition duration-300">
            <div class="flex flex-col sm:flex-row gap-5 items-start">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-500 to-indigo-650 flex items-center justify-center font-black text-white shrink-0 shadow-lg shadow-indigo-500/20 text-sm select-none">
                    02
                </div>
                <div class="space-y-3 flex-1">
                    <h3 class="text-lg font-bold text-white tracking-tight">Elige tu modo de cuenta en el Broker</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Al entrar en el panel de Alpaca, verás dos entornos claramente definidos:
                    </p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="p-4 rounded-xl bg-slate-950/40 border border-slate-900 space-y-1.5">
                            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
                                Simulado (Paper)
                            </span>
                            <p class="text-[11px] text-slate-400 leading-normal">
                                Activación inmediata. Te otorgan **$100,000 ficticios** para realizar simulaciones reales sin poner en riesgo tu dinero. Es el modo recomendado para empezar.
                            </p>
                        </div>
                        <div class="p-4 rounded-xl bg-slate-950/40 border border-slate-900 space-y-1.5">
                            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                Real (Live)
                            </span>
                            <p class="text-[11px] text-slate-400 leading-normal">
                                Requiere verificación de identidad legal (tarda entre 24 y 72 horas). Conecta tu capital real para realizar compras y ventas reales con dinero real en la bolsa de EE.UU.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 3 -->
        <div class="glass-panel rounded-2xl p-6 lg:p-8 relative overflow-hidden group hover:border-indigo-500/25 transition duration-300">
            <div class="absolute right-0 top-0 w-24 h-24 bg-indigo-500/5 rounded-full blur-2xl group-hover:bg-indigo-500/10 transition duration-300"></div>
            <div class="flex flex-col sm:flex-row gap-5 items-start">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-500 to-indigo-650 flex items-center justify-center font-black text-white shrink-0 shadow-lg shadow-indigo-500/20 text-sm select-none">
                    03
                </div>
                <div class="space-y-3 flex-1">
                    <h3 class="text-lg font-bold text-white tracking-tight">Obtén tus credenciales API Keys</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        En el panel lateral derecho de tu consola de Alpaca (asegúrate de seleccionar el modo deseado, **Paper** o **Live**):
                    </p>
                    <ol class="list-decimal pl-5 space-y-1.5 text-xs text-slate-400">
                        <li>Busca la sección llamada **"API Keys"** y haz clic en **"Generate New Keys"** (o **"View Keys"**).</li>
                        <li>Se te presentarán dos valores en pantalla:
                            <ul class="list-disc pl-5 mt-1 space-y-1">
                                <li><strong class="text-slate-200">API Key ID:</strong> Una serie de letras y números públicos.</li>
                                <li><strong class="text-slate-200">Secret Key:</strong> Una clave privada de seguridad.</li>
                            </ul>
                        </li>
                    </ol>
                    <div class="p-3.5 rounded-xl border border-amber-500/10 bg-amber-500/5 text-amber-400 text-xs font-bold leading-normal flex items-start gap-2.5">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 shrink-0 mt-0.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                        </svg>
                        <div>
                            <strong>¡ATENCIÓN!</strong> La clave "Secret Key" solo se mostrará una vez cuando la generes por primera vez. Asegúrate de copiarla y guardarla en un lugar seguro antes de cerrar esa ventana.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 4 -->
        <div class="glass-panel rounded-2xl p-6 lg:p-8 relative overflow-hidden group hover:border-indigo-500/25 transition duration-300">
            <div class="flex flex-col sm:flex-row gap-5 items-start">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-500 to-indigo-650 flex items-center justify-center font-black text-white shrink-0 shadow-lg shadow-indigo-500/20 text-sm select-none">
                    04
                </div>
                <div class="space-y-3 flex-1">
                    <h3 class="text-lg font-bold text-white tracking-tight">Guarda tus llaves en tu Perfil de GoInvesting</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Introduce las claves en el panel de configuración de perfil de nuestra plataforma. Nosotros las almacenamos de forma **encriptada y segura** para poder enviar las órdenes automáticas al broker en tu nombre.
                    </p>
                    <div class="pt-1">
                        <a href="{{ route('profile.edit') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-extrabold transition shadow-md shadow-indigo-600/10">
                            Ir a configurar mi Perfil
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3.5 h-3.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l-3 3m0 0 3 3m-3-3h12.75" />
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 5 -->
        <div class="glass-panel rounded-2xl p-6 lg:p-8 relative overflow-hidden group hover:border-indigo-500/25 transition duration-300">
            <div class="absolute right-0 top-0 w-24 h-24 bg-indigo-500/5 rounded-full blur-2xl group-hover:bg-indigo-500/10 transition duration-300"></div>
            <div class="flex flex-col sm:flex-row gap-5 items-start">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-500 to-indigo-650 flex items-center justify-center font-black text-white shrink-0 shadow-lg shadow-indigo-500/20 text-sm select-none">
                    05
                </div>
                <div class="space-y-3 flex-1">
                    <h3 class="text-lg font-bold text-white tracking-tight">¡Activa tu Bot de Trading!</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Una vez introducidas las claves, ve a tu portafolio:
                    </p>
                    <ul class="list-disc pl-5 space-y-1.5 text-xs text-slate-400">
                        <li>Elige tu modo activo (**Simulación** o **Real**) en la cabecera del portafolio.</li>
                        <li>Dirígete a la pestaña **"Bot de Trading"** y define tu estrategia (porcentaje de ganancia o pérdida, límites de gasto diario, semanal y mensual).</li>
                        <li>Haz clic en **"Ejecutar Bot"** para comenzar el escaneo y trading automático.</li>
                    </ul>
                    <div class="pt-1">
                        <a href="{{ route('portfolio') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white text-xs font-extrabold transition shadow-md shadow-emerald-600/15">
                            Ir a mi Portafolio
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3.5 h-3.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 14.25v2.25m3-4.5v4.5m3-6.75v6.75m3-9v9M6 20.25h12A2.25 2.25 0 0 0 20.25 18V6A2.25 2.25 0 0 0 18 3.75H6A2.25 2.25 0 0 0 3.75 6v12A2.25 2.25 0 0 0 6 20.25Z" />
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>
@endsection
