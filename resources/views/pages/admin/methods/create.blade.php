@extends('layouts.app')

@section('title', 'Nouveau Moyen de Paiement')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="mb-8">
        <a href="{{ route('admin.methods.index') }}" class="text-sm text-indigo-600 hover:underline flex items-center">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Retour
        </a>
        <h1 class="text-2xl font-bold text-gray-900 mt-2">Créer un nouveau moyen de paiement</h1>
    </div>

    <x-card>
        <form action="{{ route('admin.methods.store') }}" method="POST" class="space-y-6">
            @csrf
            <x-form.input name="name" label="Nom du réseau" placeholder="Ex: MTN, Moov, Celtiis..." required />

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <x-form.input name="code" label="Code interne" placeholder="Ex: mtn, moov, wave" required />

                <x-form.select name="category" label="Type de réseau">
                    <option value="mobile_money">Mobile Money</option>
                    <option value="bank">Banque</option>
                    <option value="international">International</option>
                    <option value="crypto">Crypto</option>
                    <option value="other">Autre</option>
                </x-form.select>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <x-form.input name="country_code" label="Code Pays (ISO)" placeholder="Ex: BJ, CI, TG" required />
                <x-form.input name="color_primary" label="Couleur principale" placeholder="#1F2937" />
            </div>

            <x-form.input name="logo_url" label="URL du Logo (Optionnel)" placeholder="https://..." />
            <x-form.input name="currency" label="Devise" placeholder="XOF" />

            <div class="pt-4">
                <x-button type="submit" class="w-full">Enregistrer le moyen de paiement</x-button>
            </div>
        </form>
    </x-card>
</div>
@endsection
