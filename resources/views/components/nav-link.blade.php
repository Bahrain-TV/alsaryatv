@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center px-1 pt-1 border-b-2 border-brand-maroon text-sm font-medium leading-5 text-gray-900 dark:text-cream-100 focus:outline-none focus:border-brand-deep-red transition duration-150 ease-in-out'
            : 'inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 dark:text-gray-300 hover:text-brand-maroon hover:border-brand-maroon focus:outline-none focus:text-brand-maroon focus:border-brand-maroon transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
