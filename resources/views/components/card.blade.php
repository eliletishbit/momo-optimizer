<div {{ $attributes->merge(['class' => 'bg-white rounded-2xl shadow-lg border border-gray-100 p-6']) }}>
    @if(isset($title))
        <h3 class="text-xl font-bold text-gray-900 mb-4">{{ $title }}</h3>
    @endif
    
    {{ $slot }}
</div>
