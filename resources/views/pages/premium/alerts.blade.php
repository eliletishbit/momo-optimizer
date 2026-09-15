@extends('layouts.app')

@section('title', 'Mes Alertes')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="text-center mb-12">
        <div class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-indigo-100 text-indigo-700 uppercase tracking-wider mb-4">
            💎 Fonctionnalité Premium
        </div>
        <h1 class="text-3xl font-bold text-gray-900">Alertes Frais & Promos</h1>
        <p class="mt-4 text-gray-500">Soyez notifié dès qu'un opérateur change ses tarifs ou lance une promotion flash.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <x-card title="Configurer mes alertes">
            <form action="#" class="space-y-6">
                <div class="space-y-4">
                    <label class="flex items-center justify-between p-4 border border-gray-100 rounded-2xl">
                        <span class="text-sm font-medium text-gray-700">Changement de grille tarifaire</span>
                        <input type="checkbox" checked class="rounded-full text-indigo-600 focus:ring-indigo-500 h-6 w-11 transition-all">
                    </label>
                    <label class="flex items-center justify-between p-4 border border-gray-100 rounded-2xl">
                        <span class="text-sm font-medium text-gray-700">Promotions Mobile Money</span>
                        <input type="checkbox" checked class="rounded-full text-indigo-600 focus:ring-indigo-500 h-6 w-11 transition-all">
                    </label>
                    <label class="flex items-center justify-between p-4 border border-gray-100 rounded-2xl">
                        <span class="text-sm font-medium text-gray-700">Alerte seuil d'économie</span>
                        <input type="checkbox" class="rounded-full text-indigo-600 focus:ring-indigo-500 h-6 w-11 transition-all">
                    </label>
                </div>

                <x-button class="w-full">Mettre à jour mes préférences</x-button>
            </form>
        </x-card>

        <div class="space-y-6">
            <x-card title="Canaux de notification">
                <div class="space-y-4">
                    <div class="flex items-center text-sm text-gray-600">
                        <div class="p-2 bg-indigo-50 text-indigo-600 rounded-lg mr-3">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        </div>
                        Email (Activé)
                    </div>
                    <div class="flex items-center text-sm text-gray-600">
                        <div class="p-2 bg-emerald-50 text-emerald-600 rounded-lg mr-3">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 01-2-2v6a2 2 0 012 2h2v4l.586-.586z"/></svg>
                        </div>
                        WhatsApp (Bientôt disponible)
                    </div>
                </div>
            </x-card>
        </div>
    </div>
</div>
@endsection
