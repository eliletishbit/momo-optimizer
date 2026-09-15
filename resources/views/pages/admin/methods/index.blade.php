@extends('layouts.app')

@section('title', 'Gestion des Moyens de Paiement')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-2xl font-bold text-gray-900">Moyens de Paiement</h1>
        <x-button :href="route('admin.methods.create')" tag="a">Nouveau moyen</x-button>
    </div>

    <x-card>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($methods as $method)
                <div class="p-6 border border-gray-100 rounded-2xl flex flex-col justify-between hover:shadow-md transition">
                    <div class="flex items-center justify-between mb-4">
                        <x-method-badge :method="$method" class="text-sm py-1.5" />
                        <span class="text-[10px] bg-gray-100 px-2 py-1 rounded font-bold text-gray-500 uppercase">{{ $method->country_code }}</span>
                    </div>
                    
                    <div class="flex justify-end space-x-3 mt-6 pt-4 border-t border-gray-50">
                        <a href="{{ route('admin.methods.edit', $method) }}" class="text-indigo-600 font-bold text-xs hover:underline">Éditer</a>
                        <form action="{{ route('admin.methods.destroy', $method) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-500 font-bold text-xs hover:underline" onclick="return confirm('Supprimer ce moyen ?')">Supprimer</button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="mt-8">{{ $methods->links() }}</div>
    </x-card>
</div>
@endsection
