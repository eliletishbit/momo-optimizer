@extends('layouts.app')

@section('title', 'Gestion des Frais de Réception')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-2xl font-bold text-gray-900">Frais de Réception (Retrait)</h1>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-1">
            <x-card title="Ajouter un palier">
                <form action="{{ route('admin.fees.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <input type="hidden" name="type" value="receipt">
                    <x-form.input name="country_code" label="Code pays" value="BJ" required />
                    <x-form.select name="method_id" label="Moyen de paiement">
                        @foreach(App\Models\Method::orderBy('name')->get() as $method)
                            <option value="{{ $method->id }}">{{ $method->name }}</option>
                        @endforeach
                    </x-form.select>
                    <x-form.input type="number" name="min_amount" label="Montant Min" required />
                    <x-form.input type="number" name="max_amount" label="Montant Max" required />
                    <x-form.select name="fee_type" label="Type de frais">
                        <option value="fixed">Frais fixe</option>
                        <option value="percentage">Pourcentage</option>
                    </x-form.select>
                    <x-form.input type="number" name="fee_amount" label="Frais fixe (FCFA)" value="0" />
                    <x-form.input type="number" name="fee_percentage" label="Pourcentage (%)" value="0" step="0.01" />
                    <x-button type="submit" class="w-full">Ajouter le palier</x-button>
                </form>
            </x-card>
        </div>

        <div class="lg:col-span-2">
            <x-card title="Grilles tarifaires">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Moyen</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Paliers (FCFA)</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Frais</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($fees as $fee)
                                <tr>
                                    <td class="px-4 py-4"><x-method-badge :method="$fee->method" /></td>
                                    <td class="px-4 py-4 text-sm text-gray-900 font-medium">
                                        {{ number_format($fee->min_amount, 0) }} - {{ number_format($fee->max_amount, 0) }}
                                    </td>
                                    <td class="px-4 py-4 text-sm font-bold text-indigo-600">
                                        {{ $fee->fee_amount > 0 ? number_format($fee->fee_amount, 0) . ' FCFA' : $fee->fee_percentage . '%' }}
                                    </td>
                                    <td class="px-4 py-4 text-right">
                                        <form action="{{ route('admin.fees.destroy', $fee->id) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="type" value="receipt">
                                            <button type="submit" class="text-red-500 hover:text-red-700">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">{{ $fees->links() }}</div>
            </x-card>
        </div>
    </div>
</div>
@endsection
