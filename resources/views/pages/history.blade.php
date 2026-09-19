@extends('layouts.app')

@section('title', 'Historique des optimisations')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="mb-8 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Historique des optimisations</h1>
            <p class="text-sm text-gray-500 mt-1">
                {{ $totalOptimizations }} optimisation(s) effectuée(s) • 
                {{ number_format($totalSavings, 0, ',', ' ') }} FCFA économisés
            </p>
        </div>
        <div class="flex items-center gap-3">
            @if($totalOptimizations > 0)
                <form method="POST" action="{{ route('history.clear') }}" onsubmit="return confirm('Êtes-vous sûr de vouloir réinitialiser tout votre historique d\'optimisations ? Cette action est irréversible.');">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-red-50 text-red-700 hover:bg-red-100 border border-red-200 text-sm font-semibold rounded-lg transition inline-flex items-center gap-1.5 shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Réinitialiser l'historique
                    </button>
                </form>
            @endif
            <a href="{{ route('calculator') }}" class="px-4 py-2 bg-indigo-600 text-white text-sm font-semibold rounded-lg hover:bg-indigo-700 transition inline-flex items-center gap-1.5 shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Nouvelle optimisation
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center gap-2">
            <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Montant</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Méthode</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Frais</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Économie</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($history as $opt)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $opt->created_at->format('d/m/Y H:i') }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 rounded-full text-xs font-medium {{ $opt->type === 'withdrawal' ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700' }}">
                                    {{ $opt->type === 'withdrawal' ? 'Retrait' : 'Envoi' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ number_format($opt->amount, 0, ',', ' ') }} FCFA</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $opt->selectedMethod->name ?? 'N/A' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ number_format($opt->total_fee, 0, ',', ' ') }} FCFA</td>
                            <td class="px-6 py-4 text-sm font-bold text-emerald-600">+ {{ number_format($opt->savings, 0, ',', ' ') }} FCFA</td>
                            <td class="px-6 py-4 text-right text-sm">
                                <form method="POST" action="{{ route('history.destroy', $opt->id) }}" class="inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette optimisation de l\'historique ?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-gray-400 hover:text-red-600 p-1.5 rounded-lg hover:bg-red-50 transition inline-flex items-center" title="Supprimer cette ligne">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-gray-500 text-sm">
                                Aucune optimisation effectuée pour le moment.
                                <a href="{{ route('calculator') }}" class="text-indigo-600 underline">Commencer maintenant</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($history->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $history->links() }}
            </div>
        @endif
    </div>
</div>
@endsection