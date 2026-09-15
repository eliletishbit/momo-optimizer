@extends('layouts.app')

@section('title', 'À propos')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="text-center mb-12">
        <h1 class="text-3xl font-bold text-gray-900">À propos de MomoOpti</h1>
    </div>

    <x-card class="prose prose-indigo max-w-none">
        <p class="text-lg text-gray-600">
            MomoOpti est né d'un constat simple : les frais de Mobile Money en Afrique, et particulièrement au Bénin, sont complexes et souvent élevés pour les utilisateurs quotidiens.
        </p>

        <h2 class="text-xl font-bold text-gray-900 mt-8 mb-4">Notre Mission</h2>
        <p class="text-gray-600">
            Notre mission est de démocratiser l'accès aux meilleures informations tarifaires pour permettre à chaque citoyen d'économiser sur ses transactions financières mobiles. Nous croyons que la transparence des prix est un droit fondamental.
        </p>

        <h2 class="text-xl font-bold text-gray-900 mt-8 mb-4">Comment ça marche ?</h2>
        <p class="text-gray-600">
            Nous agrégeons les données tarifaires de tous les opérateurs (MTN, Moov, Celtiis) et utilisons un algorithme d'optimisation pour vous proposer le chemin le moins coûteux pour vos envois et vos retraits. Parfois, diviser une grosse transaction en deux petites peut vous faire économiser des milliers de francs !
        </p>

        <h2 class="text-xl font-bold text-gray-900 mt-8 mb-4">L'Équipe</h2>
        <p class="text-gray-600">
            MomoOpti est développé par une équipe de passionnés de la Fintech basés à Cotonou, désireux d'apporter des solutions concrètes aux problèmes locaux grâce à la technologie.
        </p>
    </x-card>
</div>
@endsection
