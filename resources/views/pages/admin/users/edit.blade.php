@extends('layouts.app')

@section('title', 'Modifier Utilisateur')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="mb-8">
        <a href="{{ route('admin.users.index') }}" class="text-sm text-indigo-600 hover:underline flex items-center">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Retour à la liste
        </a>
        <h1 class="text-2xl font-bold text-gray-900 mt-2">Modifier l'utilisateur : {{ $user->name }}</h1>
    </div>

    <x-card>
        <form action="{{ route('admin.users.update', $user) }}" method="POST" class="space-y-6">
            @csrf
            @method('PATCH')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <x-form.input name="name" label="Nom" :value="$user->name" required />
                <x-form.input name="email" type="email" label="Email" :value="$user->email" required />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <x-form.select name="subscription" label="Plan d'abonnement">
                    <option value="free" {{ $user->subscription == 'free' ? 'selected' : '' }}>Gratuit (Free)</option>
                    <option value="premium" {{ $user->subscription == 'premium' ? 'selected' : '' }}>Premium</option>
                    <option value="pro" {{ $user->subscription == 'pro' ? 'selected' : '' }}>Pro</option>
                    <option value="business" {{ $user->subscription == 'business' ? 'selected' : '' }}>Business</option>
                </x-form.select>

                <x-form.select name="is_admin" label="Rôle">
                    <option value="0" {{ !$user->is_admin ? 'selected' : '' }}>Utilisateur standard</option>
                    <option value="1" {{ $user->is_admin ? 'selected' : '' }}>Administrateur</option>
                </x-form.select>
            </div>

            <div class="pt-4">
                <x-button type="submit" class="w-full">Enregistrer les modifications</x-button>
            </div>
        </form>
    </x-card>
</div>
@endsection
