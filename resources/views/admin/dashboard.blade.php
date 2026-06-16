@extends('layouts.layout')

@section('title', 'Panel de Control Admin | GoInvesting')

@section('content')
<div class="space-y-8">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl lg:text-3xl font-extrabold text-white tracking-tight">Panel de Control de Administración</h1>
            <p class="text-sm text-slate-400 font-medium">Gestión de usuarios, roles y límites de gasto financiero</p>
        </div>
        <a href="{{ route('portfolio') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-400 hover:text-white transition">
            Volver a Portafolio
        </a>
    </div>

    <!-- Alerts -->
    @if(session('success'))
        <div class="glass-panel rounded-2xl p-4 border-green-500/25 bg-green-500/5 text-green-400 text-sm font-semibold flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="glass-panel rounded-2xl p-4 border-red-500/25 bg-red-500/5 text-red-400 text-sm font-semibold space-y-1">
            @foreach($errors->all() as $error)
                <div class="flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                    </svg>
                    {{ $error }}
                </div>
            @endforeach
        </div>
    @endif

    <!-- Create User Form -->
    <div class="glass-panel rounded-2xl p-6 shadow-xl space-y-4">
        <h2 class="text-lg font-extrabold text-white flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-indigo-400">
                <path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z" />
            </svg>
            Crear Nuevo Usuario
        </h2>
        <form action="{{ route('admin.user.create') }}" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @csrf
            <div class="space-y-1">
                <label class="text-xs font-bold text-slate-400 block">Nombre Completo</label>
                <input type="text" name="name" required placeholder="Nombre del usuario" class="w-full bg-slate-950/70 border border-slate-800 rounded-xl py-2 px-3.5 text-xs text-slate-200 placeholder-slate-600 focus:outline-none focus:border-indigo-500">
            </div>
            <div class="space-y-1">
                <label class="text-xs font-bold text-slate-400 block">Correo Electrónico</label>
                <input type="email" name="email" required placeholder="correo@ejemplo.com" class="w-full bg-slate-950/70 border border-slate-800 rounded-xl py-2 px-3.5 text-xs text-slate-200 placeholder-slate-600 focus:outline-none focus:border-indigo-500">
            </div>
            <div class="space-y-1">
                <label class="text-xs font-bold text-slate-400 block">Contraseña Temporal</label>
                <input type="password" name="password" required placeholder="Mínimo 8 caracteres" class="w-full bg-slate-950/70 border border-slate-800 rounded-xl py-2 px-3.5 text-xs text-slate-200 placeholder-slate-600 focus:outline-none focus:border-indigo-500">
            </div>
            <div class="space-y-1">
                <label class="text-xs font-bold text-slate-400 block">Rol del Usuario</label>
                <select name="role" required class="w-full bg-slate-950/70 border border-slate-800 rounded-xl py-2 px-3 text-xs text-slate-200 focus:outline-none focus:border-indigo-500">
                    <option value="investor">Inversor (Investor)</option>
                    <option value="admin">Administrador (Admin)</option>
                </select>
            </div>
            <div class="space-y-1">
                <label class="text-xs font-bold text-slate-400 block">Límite de Gasto Diario ($)</label>
                <input type="number" step="0.01" name="daily_spend_limit" placeholder="Ej: 5000 (Opcional)" class="w-full bg-slate-950/70 border border-slate-800 rounded-xl py-2 px-3.5 text-xs text-slate-200 placeholder-slate-600 focus:outline-none focus:border-indigo-500">
            </div>
            <div class="space-y-1">
                <label class="text-xs font-bold text-slate-400 block">Límite de Gasto Semanal ($)</label>
                <input type="number" step="0.01" name="weekly_spend_limit" placeholder="Ej: 25000 (Opcional)" class="w-full bg-slate-950/70 border border-slate-800 rounded-xl py-2 px-3.5 text-xs text-slate-200 placeholder-slate-600 focus:outline-none focus:border-indigo-500">
            </div>
            <div class="md:col-span-3 flex justify-end pt-2">
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-indigo-600 text-white font-extrabold text-xs shadow-md shadow-indigo-600/20 hover:bg-indigo-500 transition">
                    Crear y Registrar Usuario
                </button>
            </div>
        </form>
    </div>

    <!-- Users Management Table -->
    <div class="space-y-4">
        <h2 class="text-lg font-extrabold text-white flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-indigo-400">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.109A2.25 2.25 0 0 1 12.75 21.5h-1.5a2.25 2.25 0 0 1-2.25-2.263V19.13m4.786-3.07a9.348 9.348 0 0 0-2.813-1.893m0 0a4.124 4.124 0 0 1-8.114-1.876 4.125 4.125 0 0 1 7.533-2.493m-1.14 2.397a9.348 9.348 0 0 0-1.41-1.875M11.25 7.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0ZM19.5 9.75a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
            </svg>
            Usuarios Registrados
        </h2>

        <div class="glass-panel rounded-2xl overflow-hidden shadow-xl">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse min-w-[900px]">
                    <thead>
                        <tr class="border-b border-slate-900 text-xs font-bold uppercase tracking-wider text-slate-500 bg-[#070913]/30">
                            <th class="py-4 px-5">ID</th>
                            <th class="py-4 px-5">Usuario</th>
                            <th class="py-4 px-5">Email</th>
                            <th class="py-4 px-5">Rol Actual</th>
                            <th class="py-4 px-5">Límite Diario</th>
                            <th class="py-4 px-5">Límite Semanal</th>
                            <th class="py-4 px-5 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-900/50">
                        @foreach($users as $user)
                            <tr class="hover:bg-slate-950/20 transition">
                                <td class="py-4 px-5 font-bold text-xs text-slate-500">{{ $user->id }}</td>
                                <td class="py-4 px-5">
                                    <div class="font-bold text-sm text-white">{{ $user->name }}</div>
                                    <div class="text-[10px] text-slate-500 font-mono">Registrado: {{ $user->created_at->format('d/m/Y') }}</div>
                                </td>
                                <td class="py-4 px-5 text-slate-300 text-sm font-medium">{{ $user->email }}</td>
                                <td class="py-4 px-5">
                                    <form action="{{ route('admin.update-role', $user->id) }}" method="POST" class="inline-flex items-center gap-2">
                                        @csrf
                                        <select name="role" onchange="this.form.submit()" class="bg-slate-950 border border-slate-800 rounded-lg text-xs font-semibold text-slate-300 py-1.5 px-2.5 focus:outline-none focus:border-indigo-500">
                                            <option value="investor" {{ $user->role === 'investor' ? 'selected' : '' }}>Inversor</option>
                                            <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Administrador</option>
                                        </select>
                                    </form>
                                </td>
                                <td class="py-4 px-5 font-semibold text-slate-200 text-sm">
                                    ${{ number_format($user->daily_spend_limit ?? 0, 2) }}
                                </td>
                                <td class="py-4 px-5 font-semibold text-slate-200 text-sm">
                                    ${{ number_format($user->weekly_spend_limit ?? 0, 2) }}
                                </td>
                                <td class="py-4 px-5">
                                    <form action="{{ route('admin.update-limits', $user->id) }}" method="POST" class="flex items-center justify-end gap-2">
                                        @csrf
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-[10px] text-slate-500 font-bold uppercase">Día:</span>
                                            <input type="number" step="0.01" name="daily_spend_limit" value="{{ $user->daily_spend_limit }}" placeholder="Sin límite" class="w-24 bg-slate-950/70 border border-slate-800 rounded-lg py-1 px-2 text-xs text-slate-200 focus:outline-none focus:border-indigo-500">
                                        </div>
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-[10px] text-slate-500 font-bold uppercase">Sem:</span>
                                            <input type="number" step="0.01" name="weekly_spend_limit" value="{{ $user->weekly_spend_limit }}" placeholder="Sin límite" class="w-24 bg-slate-950/70 border border-slate-800 rounded-lg py-1 px-2 text-xs text-slate-200 focus:outline-none focus:border-indigo-500">
                                        </div>
                                        <button type="submit" class="px-2.5 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs shadow-md transition">
                                            Guardar
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
