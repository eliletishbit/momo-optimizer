@extends('layouts.app')

@section('title', 'Contact')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="text-center mb-12">
        <h1 class="text-3xl font-bold text-gray-900">Contactez-nous</h1>
        <p class="mt-4 text-gray-500">Une question ? Un partenariat ? Nous sommes à votre écoute.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <div class="md:col-span-1 space-y-6">
            <x-card>
                <h4 class="font-bold text-gray-900 mb-2">Notre Bureau</h4>
                <p class="text-sm text-gray-500">
                    Cotonou, Bénin<br>
                    Quartier Akpakpa, Immeuble Horizon
                </p>
            </x-card>
            <x-card>
                <h4 class="font-bold text-gray-900 mb-2">Email</h4>
                <p class="text-sm text-gray-500">
                    contact@momoopti.bj
                </p>
            </x-card>
            <x-card>
                <h4 class="font-bold text-gray-900 mb-2">Téléphone</h4>
                <p class="text-sm text-gray-500">
                    +229 00 00 00 00
                </p>
            </x-card>
        </div>

        <div class="md:col-span-2">
            <x-card>
                <form action="#" method="POST" class="space-y-6">
                    @csrf
                    <x-form.input name="name" label="Nom Complet" placeholder="Votre nom" required />
                    <x-form.input type="email" name="email" label="Email" placeholder="votre@email.com" required />
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Message</label>
                        <textarea name="message" rows="4" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all" placeholder="Comment pouvons-nous vous aider ?"></textarea>
                    </div>

                    <x-button type="submit" class="w-full">
                        Envoyer le message
                    </x-button>
                </form>
            </x-card>
        </div>
    </div>
</div>
@endsection
