@extends('layouts.app')

@section('title', 'Gestion des Abonnements')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900">Abonnements</h1>
        <p class="text-sm text-gray-500">Suivi des abonnements payants de la plateforme.</p>
    </div>

    <x-card>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Utilisateur</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Expire le</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($subscriptions as $sub)
                        <tr>
                            <td class="px-6 py-4">
                                <div class="text-sm font-bold text-gray-900">{{ $sub->user->name }}</div>
                                <div class="text-xs text-gray-500">{{ $sub->user->email }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-[10px] font-bold rounded-full {{ $sub->status == 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }} uppercase">
                                    {{ $sub->status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-xs text-gray-500">
                                {{ $sub->ends_at ? $sub->ends_at->format('d/m/Y') : 'N/A' }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <form action="{{ route('admin.subscriptions.update', $sub) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button name="status" value="expired" class="text-xs font-bold text-amber-600 hover:underline">Suspendre</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-6">{{ $subscriptions->links() }}</div>
    </x-card>
</div>
@endsection
