@props(['method'])

@php
$colors = [
    'MTN' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
    'MOOV' => 'bg-blue-100 text-blue-800 border-blue-200',
    'CELTIIS' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
    'WAVE' => 'bg-sky-100 text-sky-800 border-sky-200',
];
$colorClass = $colors[strtoupper($method->name)] ?? 'bg-gray-100 text-gray-800 border-gray-200';
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center px-3 py-1 rounded-full text-xs font-bold border ' . $colorClass]) }}>
    @if(isset($method->logo_url))
        <img src="{{ $method->logo_url }}" class="w-4 h-4 mr-2 rounded-full" alt="">
    @endif
    {{ strtoupper($method->name) }}
</span>
