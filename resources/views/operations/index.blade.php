@extends('layouts.app')

@section('title', 'Mes opérations')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">📊 Mes opérations</h1>
            <p class="text-sm text-gray-500">Gestion de vos opérations en temps réel</p>
        </div>
        <div class="flex gap-3 flex-wrap">
            <a href="{{ route('operations.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-xl hover:bg-indigo-700 transition">
                ➕ Nouvelle opération
            </a>
            <a href="{{ route('operations.bilan') }}" class="bg-emerald-600 text-white px-4 py-2 rounded-xl hover:bg-emerald-700 transition">
                📈 Bilan journalier
            </a>
            <a href="{{ route('operations.caisses.edit') }}" class="bg-blue-600 text-white px-4 py-2 rounded-xl hover:bg-blue-700 transition">
                💰 Gérer les caisses
            </a>
        </div>
    </div>

    {{-- Filtres --}}
    <div class="bg-white rounded-2xl shadow p-4 mb-6">
        <form method="GET" class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div>
                <label class="text-sm font-semibold text-gray-700">Date</label>
                <input type="date" name="date" value="{{ request('date') }}" class="w-full rounded-xl border-gray-300">
            </div>
            <div>
                <label class="text-sm font-semibold text-gray-700">Réseau</label>
                <select name="reseau" class="w-full rounded-xl border-gray-300">
                    <option value="">Tous</option>
                    @foreach($reseaux as $reseau)
                        <option value="{{ $reseau }}" {{ request('reseau') == $reseau ? 'selected' : '' }}>{{ $reseau }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-sm font-semibold text-gray-700">Type</label>
                <select name="type_operation_id" class="w-full rounded-xl border-gray-300">
                    <option value="">Tous</option>
                    @foreach($types as $type)
                        <option value="{{ $type->id }}" {{ request('type_operation_id') == $type->id ? 'selected' : '' }}>{{ $type->nom }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-sm font-semibold text-gray-700">Direction</label>
                <select name="direction" class="w-full rounded-xl border-gray-300">
                    <option value="">Toutes</option>
                    <option value="entrant" {{ request('direction') == 'entrant' ? 'selected' : '' }}>📥 Entrant</option>
                    <option value="sortant" {{ request('direction') == 'sortant' ? 'selected' : '' }}>📤 Sortant</option>
                </select>
            </div>
            <div class="col-span-2 md:col-span-4 flex justify-end">
                <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-xl hover:bg-indigo-700 transition">
                    Filtrer
                </button>
                <a href="{{ route('operations.index') }}" class="ml-2 bg-gray-200 text-gray-700 px-6 py-2 rounded-xl hover:bg-gray-300 transition">
                    Réinitialiser
                </a>
            </div>
        </form>
    </div>

    {{-- Tableau --}}
    <div class="bg-white rounded-2xl shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">N°</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Réseau</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Direction</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Montant</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Client</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($operations as $operation)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm">{{ $loop->iteration }}</td>
                        <td class="px-6 py-4 text-sm">{{ $operation->typeoperateur->nom ?? 'N/A' }}</td>
                        <td class="px-6 py-4 text-sm">
                            <span class="px-2 py-1 bg-gray-100 rounded-full text-xs">{{ $operation->reseau }}</span>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            @if($operation->direction === 'entrant')
                                <span class="text-emerald-600 font-semibold">📥 Entrant</span>
                            @else
                                <span class="text-orange-600 font-semibold">📤 Sortant</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm font-semibold">{{ number_format($operation->montant, 0, ',', ' ') }} FCFA</td>
                        <td class="px-6 py-4 text-sm">{{ $operation->telephone_client ?? '-' }}</td>
                        <td class="px-6 py-4 text-sm">{{ $operation->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-6 py-4 text-sm">
                            <form action="{{ route('operations.destroy', $operation->id) }}" method="POST" onsubmit="return confirm('Supprimer cette opération ?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800 transition">
                                    🗑️
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                            Aucune opération enregistrée
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-6 py-4">
            {{ $operations->links() }}
        </div>
    </div>
</div>
@endsection