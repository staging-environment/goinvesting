<!DOCTYPE html>
<html lang="es" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>GoInvesting | Plataforma Financiera en Tiempo Real</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <!-- Custom Premium Styles -->
        <style>
            body {
                font-family: 'Plus Jakarta Sans', 'Outfit', sans-serif;
                background-color: #070913;
                color: #f8fafc;
                overflow-x: hidden;
            }
            
            .glass-panel {
                background: rgba(15, 23, 42, 0.45);
                backdrop-filter: blur(12px);
                -webkit-backdrop-filter: blur(12px);
                border: 1px solid rgba(255, 255, 255, 0.06);
            }
        </style>
    </head>
    <body class="font-sans antialiased text-slate-100 bg-[#070913]">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 px-4">
            <div class="mb-6">
                <!-- Logo GoInvesting -->
                <a href="/" class="flex items-center gap-2.5 select-none shrink-0 group">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-indigo-600 to-violet-500 flex items-center justify-center shadow-lg shadow-indigo-550/20 group-hover:scale-105 transition-all duration-300">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-6 h-6 text-white">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.307a11.95 11.95 0 0 0 5.814-5.519l2.74-1.22m0 0-5.94-2.28m5.94 2.28-2.28 5.941" />
                        </svg>
                    </div>
                    <span class="font-extrabold text-2xl tracking-tight flex items-center select-none">
                        <span class="bg-gradient-to-r from-white to-slate-200 bg-clip-text text-transparent">Go</span>
                        <span class="bg-gradient-to-r from-indigo-300 to-violet-400 bg-clip-text text-transparent">Investing</span>
                    </span>
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-2 px-8 py-8 glass-panel shadow-2xl rounded-2xl overflow-hidden">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>

