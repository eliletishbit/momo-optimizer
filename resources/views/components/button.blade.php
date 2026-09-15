@props(['variant' => 'primary', 'tag' => 'button', 'href' => null])

@php
$classes = 'inline-flex items-center justify-center font-semibold py-3 px-6 rounded-xl transition-all shadow-md hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-offset-2 ';

switch ($variant) {
    case 'secondary':
        $classes .= 'bg-white hover:bg-gray-50 text-gray-700 border border-gray-300 focus:ring-gray-500';
        break;
    case 'success':
        $classes .= 'bg-emerald-600 hover:bg-emerald-700 text-white focus:ring-emerald-500';
        break;
    case 'danger':
        $classes .= 'bg-red-600 hover:bg-red-700 text-white focus:ring-red-500';
        break;
    case 'primary':
    default:
        $classes .= 'bg-indigo-600 hover:bg-indigo-700 text-white focus:ring-indigo-500';
        break;
}

$componentTag = $tag === 'a' && $href ? 'a' : 'button';
$attributes = $attributes->class([$classes]);
@endphp

@if($componentTag === 'a')
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </button>
@endif
