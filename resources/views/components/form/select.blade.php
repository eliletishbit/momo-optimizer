@props(['label' => null, 'options' => []])

<div>
    @if($label)
        <label class="block text-sm font-medium text-gray-700 mb-1">
            {{ $label }}
        </label>
    @endif
    <select {!! $attributes->merge(['class' => 'w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all']) !!}>
        {{ $slot }}
    </select>
    @error($attributes->get('name'))
        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
    @enderror
</div>
