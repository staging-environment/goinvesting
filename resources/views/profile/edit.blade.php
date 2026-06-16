@extends('layouts.layout')

@section('title', 'Mi Perfil | GoInvesting')

@section('content')
<div class="space-y-8 max-w-3xl mx-auto">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl lg:text-3xl font-extrabold text-white tracking-tight">Mi Perfil</h1>
            <p class="text-sm text-slate-400">Gestiona la información de tu cuenta y credenciales de trading</p>
        </div>
        <a href="{{ route('portfolio') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-400 hover:text-white transition">
            Volver a Portafolio
        </a>
    </div>

    <!-- Alerts -->
    @if(session('success') || session('status') === 'profile-updated' || session('status') === 'alpaca-updated-success' || session('status') === 'bot-strategy-updated' || session('status') === 'password-updated')
        <div class="glass-panel border-green-500/20 bg-green-500/5 rounded-2xl p-4 text-sm text-green-400 font-bold flex items-center gap-3">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1 3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z" />
            </svg>
            <div>
                @if(session('success'))
                    {{ session('success') }}
                @elseif(session('status') === 'profile-updated')
                    Los datos de tu perfil han sido actualizados correctamente.
                @elseif(session('status') === 'alpaca-updated-success')
                    Credenciales de Alpaca verificadas y guardadas con éxito. Conexión establecida.
                @elseif(session('status') === 'bot-strategy-updated')
                    La configuración de la estrategia y límites de gasto del bot se ha actualizado correctamente.
                @elseif(session('status') === 'password-updated')
                    Tu contraseña ha sido actualizada correctamente.
                @endif
            </div>
        </div>
    @endif

    @if(session('status') === 'alpaca-updated-error' || session('error') || $errors->any())
        <div class="glass-panel border-red-500/20 bg-red-500/5 rounded-2xl p-4 text-sm text-red-400 font-bold flex items-center gap-3">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
            </svg>
            <div>
                @if(session('status') === 'alpaca-updated-error')
                    {!! session('alpaca_error_msg') !!}
                @else
                    {!! session('error') ?? $errors->first() !!}
                @endif
            </div>
        </div>
    @endif

    <!-- Profile Info Block -->
    <div class="glass-panel rounded-2xl p-6 sm:p-8 shadow-xl">
        @include('profile.partials.update-profile-information-form')
    </div>

    <!-- Alpaca Credentials Block -->
    <div class="glass-panel rounded-2xl p-6 sm:p-8 shadow-xl">
        @include('profile.partials.update-alpaca-credentials-form')
    </div>

    <!-- Bot Strategy Configuration Block -->
    <div class="glass-panel rounded-2xl p-6 sm:p-8 shadow-xl">
        @include('profile.partials.update-bot-strategy-form')
    </div>


    <!-- Password Update Block -->
    <div class="glass-panel rounded-2xl p-6 sm:p-8 shadow-xl">
        @include('profile.partials.update-password-form')
    </div>

    <!-- Delete Account Block -->
    <div class="glass-panel rounded-2xl p-6 sm:p-8 shadow-xl border-red-500/20 bg-red-500/5">
        @include('profile.partials.delete-user-form')
    </div>
</div>
@endsection
