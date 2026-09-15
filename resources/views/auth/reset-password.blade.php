@extends('layouts.guest')

@section('title', 'Réinitialiser le mot de passe')

@section('content')
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-gray-900">Nouveau mot de passe</h2>
        <p class="text-gray-500 mt-2 text-sm">Choisissez un mot de passe sécurisé pour votre compte.</p>
    </div>

    <form method="POST" action="{{ route('password.store') }}" class="space-y-6">
        @csrf

        <!-- Password Reset Token -->
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <!-- Email Address -->
        <x-form.input id="email" type="email" name="email" :value="old('email', $request->email)" label="Adresse Email" required autofocus autocomplete="username" />

        <!-- Password -->
        <x-form.input id="password" type="password" name="password" label="Nouveau mot de passe" placeholder="••••••••" required autocomplete="new-password" />

        <!-- Confirm Password -->
        <x-form.input id="password_confirmation" type="password" name="password_confirmation" label="Confirmer le mot de passe" placeholder="••••••••" required autocomplete="new-password" />

        <div class="pt-2">
            <x-button class="w-full py-4 text-base">
                Réinitialiser le mot de passe
            </x-button>
        </div>
    </form>
@endsection
