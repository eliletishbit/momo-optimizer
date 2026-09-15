@extends('layouts.app')

@section('title', 'Mon Profil')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <h1 class="text-2xl font-bold text-gray-900 mb-8">Paramètres du profil</h1>

    <div class="space-y-8">
        <!-- Profile Information -->
        <x-card title="Informations personnelles">
            <form method="post" action="{{ route('profile.update') }}" class="space-y-6">
                @csrf
                @method('patch')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <x-form.input name="name" label="Nom" :value="old('name', $user->name)" required />
                    <x-form.input name="email" type="email" label="Adresse Email" :value="old('email', $user->email)" required />
                </div>

                <div class="flex items-center gap-4">
                    <x-button type="submit">Enregistrer les modifications</x-button>
                </div>
            </form>
        </x-card>

        <!-- Update Password -->
        <x-card title="Changer le mot de passe">
            <form method="post" action="{{ route('password.update') }}" class="space-y-6">
                @csrf
                @method('put')

                <x-form.input name="current_password" type="password" label="Mot de passe actuel" required />
                <x-form.input name="password" type="password" label="Nouveau mot de passe" required />
                <x-form.input name="password_confirmation" type="password" label="Confirmer le mot de passe" required />

                <div class="flex items-center gap-4">
                    <x-button type="submit">Mettre à jour le mot de passe</x-button>
                </div>
            </form>
        </x-card>

        <!-- Delete Account -->
        <x-card title="Supprimer le compte" class="border-red-100">
            <div class="text-sm text-gray-500 mb-6">
                Une fois votre compte supprimé, toutes ses ressources et données seront définitivement effacées.
            </div>

            <x-button variant="danger" x-data="" x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')">
                Supprimer mon compte
            </x-button>
        </x-card>
    </div>
</div>
@endsection
