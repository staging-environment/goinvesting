@extends('layouts.layout')

@section('title', 'Panel de Control Admin | GoInvesting')

@section('content')
<div class="space-y-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl lg:text-3xl font-extrabold text-white tracking-tight">Panel de Control de Administración</h1>
            <p class="text-sm text-slate-400 font-medium">Gestión de usuarios, roles y límites de gasto financiero</p>
        </div>
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.user.create-form') }}" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-extrabold text-xs shadow-md shadow-indigo-600/20 transition">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3.5 h-3.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Crear Nuevo Usuario
            </a>
            <a href="{{ route('portfolio') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-400 hover:text-white transition">
                Volver a Portafolio
            </a>
        </div>
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
                <table class="w-full text-left border-collapse min-w-[800px]">
                    <thead>
                        <tr class="border-b border-slate-900 text-xs font-bold uppercase tracking-wider text-slate-500 bg-[#070913]/30">
                            <th class="py-4 px-5">ID</th>
                            <th class="py-4 px-5">Usuario</th>
                            <th class="py-4 px-5">Email</th>
                            <th class="py-4 px-5">Rol</th>
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
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $user->role === 'admin' ? 'bg-indigo-500/10 text-indigo-400 border border-indigo-500/20' : 'bg-slate-500/10 text-slate-400 border border-slate-550/20' }}">
                                        {{ $user->role === 'admin' ? 'Administrador' : 'Inversor' }}
                                    </span>
                                </td>
                                <td class="py-4 px-5 font-semibold text-slate-200 text-sm">
                                    ${{ number_format($user->daily_spend_limit ?? 0, 2) }}
                                </td>
                                <td class="py-4 px-5 font-semibold text-slate-200 text-sm">
                                    ${{ number_format($user->weekly_spend_limit ?? 0, 2) }}
                                </td>
                                <td class="py-4 px-5 text-right">
                                    <a href="{{ route('admin.user.edit-form', $user->id) }}" class="inline-flex items-center px-3.5 py-1.5 rounded-lg bg-indigo-650 hover:bg-indigo-500 text-white font-extrabold text-xs shadow-md transition">
                                        Editar
                                    </a>
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
