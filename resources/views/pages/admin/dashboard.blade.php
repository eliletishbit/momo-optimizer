@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900">Tableau de bord Administrateur</h1>
        <p class="text-sm text-gray-500">Vue d'ensemble de l'activité de MomoOpti.</p>
    </div>

    <!-- Admin KPIs -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-12">
        <x-card class="bg-indigo-600 text-white border-indigo-600 shadow-indigo-500/20">
            <p class="text-indigo-100 text-xs font-bold uppercase tracking-wider">Utilisateurs</p>
            <p class="text-3xl font-bold mt-1 text-white">{{ $totalUsers }}</p>
        </x-card>
        <x-card>
            <p class="text-gray-500 text-xs font-bold uppercase tracking-wider">Abonnements Actifs</p>
            <p class="text-3xl font-bold mt-1 text-gray-900">{{ $activeSubscriptions }}</p>
        </x-card>
        <x-card>
            <p class="text-gray-500 text-xs font-bold uppercase tracking-wider">Optimisations totales</p>
            <p class="text-3xl font-bold mt-1 text-gray-900">{{ $totalOptimizations }}</p>
        </x-card>
        <x-card>
            <p class="text-gray-500 text-xs font-bold uppercase tracking-wider">Chiffre d'Affaires</p>
            <p class="text-3xl font-bold mt-1 text-emerald-600">-- FCFA</p>
        </x-card>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Recent Users -->
        <x-card title="Derniers inscrits">
            <div class="space-y-4">
                @foreach($recentUsers as $user)
                    <div class="flex items-center justify-between pb-4 border-b border-gray-50 last:border-0 last:pb-0">
                        <div>
                            <p class="text-sm font-bold text-gray-900">{{ $user->name }}</p>
                            <p class="text-xs text-gray-500">{{ $user->email }}</p>
                        </div>
                        <span class="text-[10px] bg-gray-100 text-gray-600 px-2 py-1 rounded font-bold uppercase">
                            {{ $user->created_at->diffForHumans() }}
                        </span>
                    </div>
                @endforeach
            </div>
            <div class="mt-6 text-center">
                <a href="{{ route('admin.users.index') }}" class="text-sm text-indigo-600 font-bold hover:underline">Voir tous les utilisateurs</a>
            </div>
        </x-card>

        <!-- Quick Links -->
        <x-card title="Gestion rapide">
            <div class="grid grid-cols-2 gap-4">
                <a href="{{ route('admin.fees.receipt') }}" class="p-4 bg-gray-50 rounded-2xl hover:bg-indigo-50 hover:text-indigo-600 transition group text-center">
                    <p class="font-bold text-sm">Gérer les frais</p>
                </a>
                <a href="{{ route('admin.methods.index') }}" class="p-4 bg-gray-50 rounded-2xl hover:bg-indigo-50 hover:text-indigo-600 transition group text-center">
                    <p class="font-bold text-sm">Gérer les réseaux</p>
                </a>
                <a href="{{ route('admin.subscriptions.index') }}" class="p-4 bg-gray-50 rounded-2xl hover:bg-indigo-50 hover:text-indigo-600 transition group text-center">
                    <p class="font-bold text-sm">Abonnements</p>
                </a>
                <a href="{{ route('admin.statistics') }}" class="p-4 bg-gray-50 rounded-2xl hover:bg-indigo-50 hover:text-indigo-600 transition group text-center">
                    <p class="font-bold text-sm">Statistiques</p>
                </a>
            </div>
        </x-card>
    </div>
</div>
@endsection
