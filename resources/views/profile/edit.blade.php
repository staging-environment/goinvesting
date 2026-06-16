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
