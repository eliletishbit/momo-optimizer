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
        <a href="{{ route('calculator') }}" class="px-4 py-2 bg-indigo-600 text-white text-sm font-semibold rounded-lg hover:bg-indigo-700">
            Nouvelle optimisation
        </a>
    </div>

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
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($history as $opt)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $opt->created_at->format('d/m/Y H:i') }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 rounded-full text-xs {{ $opt->type === 'withdrawal' ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700' }}">
                                    {{ $opt->type === 'withdrawal' ? 'Retrait' : 'Envoi' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ number_format($opt->amount, 0, ',', ' ') }} FCFA</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $opt->selectedMethod->name ?? 'N/A' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ number_format($opt->total_fee, 0, ',', ' ') }} FCFA</td>
                            <td class="px-6 py-4 text-sm font-bold text-emerald-600">+ {{ number_format($opt->savings, 0, ',', ' ') }} FCFA</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-500 text-sm">
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