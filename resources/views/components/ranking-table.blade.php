@props(['results' => []])

<div class="overflow-x-auto rounded-xl border border-gray-100 shadow-sm">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rang</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Réseau / Moyen</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Frais</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Économie</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-100">
            @forelse($results as $result)
                <tr class="{{ $loop->first ? 'bg-emerald-50/50' : '' }}">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold">
                        @if($loop->first) 🥇 @elseif($loop->iteration == 2) 🥈 @elseif($loop->iteration == 3) 🥉 @else {{ $loop->iteration }} @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <x-method-badge :method="$result['method']" />
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                        {{ number_format($result['fee'], 0, ',', ' ') }} FCFA
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-emerald-600 font-semibold">
                        - {{ number_format($result['savings'] ?? 0, 0, ',', ' ') }} FCFA
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                        Aucun résultat à afficher.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
