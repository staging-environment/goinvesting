@extends('layouts.layout')

@section('title', 'Editar Usuario | GoInvesting')

@section('content')
<div class="space-y-8 max-w-2xl mx-auto">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl lg:text-3xl font-extrabold text-white tracking-tight">Editar Usuario: {{ $user->name }}</h1>
            <p class="text-sm text-slate-400 font-medium">Modifica los datos del usuario, su rol y sus límites de gasto financiero</p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-400 hover:text-white transition">
            Volver a la Lista
        </a>
    </div>

    <!-- Error Alerts -->
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

    <!-- Edit User Form Card -->
    <div class="glass-panel rounded-2xl p-6 sm:p-8 shadow-xl">
        <form action="{{ route('admin.user.update', $user->id) }}" method="POST" class="space-y-6">
            @csrf
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div class="space-y-1">
                    <x-input-label for="name" :value="__('Nombre Completo')" />
                    <x-text-input id="name" type="text" name="name" required placeholder="Nombre del usuario" class="w-full" :value="old('name', $user->name)" />
                    <x-input-error class="mt-2" :messages="$errors->get('name')" />
                </div>
                
                <div class="space-y-1">
                    <x-input-label for="email" :value="__('Correo Electrónico')" />
                    <x-text-input id="email" type="email" name="email" required placeholder="correo@ejemplo.com" class="w-full" :value="old('email', $user->email)" />
                    <x-input-error class="mt-2" :messages="$errors->get('email')" />
                </div>

                <div class="space-y-1">
                    <x-input-label for="password" :value="__('Contraseña (Dejar en blanco para no cambiar)')" />
                    <x-text-input id="password" type="password" name="password" placeholder="Nueva contraseña (opcional)" class="w-full" />
                    <x-input-error class="mt-2" :messages="$errors->get('password')" />
                </div>

                <div class="space-y-1">
                    <x-input-label for="role" :value="__('Rol')" />
                    <select id="role" name="role" required class="w-full bg-slate-950/70 border border-slate-800 rounded-xl py-2 px-3 text-sm text-slate-200 focus:outline-none focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="investor" {{ old('role', $user->role) === 'investor' ? 'selected' : '' }}>Inversor</option>
                        <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Administrador</option>
                    </select>
                    <x-input-error class="mt-2" :messages="$errors->get('role')" />
                </div>

                <div class="space-y-1">
                    <x-input-label for="daily_spend_limit" :value="__('Límite de Gasto Diario ($)')" />
                    <x-text-input id="daily_spend_limit" type="number" step="0.01" name="daily_spend_limit" placeholder="Ej: 5000 (Opcional)" class="w-full" :value="old('daily_spend_limit', $user->daily_spend_limit)" />
                    <x-input-error class="mt-2" :messages="$errors->get('daily_spend_limit')" />
                </div>

                <div class="space-y-1">
                    <x-input-label for="weekly_spend_limit" :value="__('Límite de Gasto Semanal ($)')" />
                    <x-text-input id="weekly_spend_limit" type="number" step="0.01" name="weekly_spend_limit" placeholder="Ej: 25000 (Opcional)" class="w-full" :value="old('weekly_spend_limit', $user->weekly_spend_limit)" />
                    <x-input-error class="mt-2" :messages="$errors->get('weekly_spend_limit')" />
                </div>
            </div>

            <div class="flex justify-end pt-4">
                <x-primary-button>
                    Guardar Cambios
                </x-primary-button>
            </div>
        </form>
    </div>
</div>
@endsection
