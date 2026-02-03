@props([
    'name',
    'label',
    'type' => 'text',
    'value' => '',
    'placeholder' => ''
])

<div class="mt-5">
    <label for="{{ $name }}" class="block text-right text-base sm:text-lg font-bold text-white mb-5 drop-shadow-lg">
        {{ $label }}
    </label>

    <input
        type="{{ $type }}"
        id="{{ $name }}"
        name="{{ $name }}"
        value="{{ $value }}"
        placeholder="{{ $placeholder }}"
        {{ $attributes->merge([
            'class' => 'w-full px-4 py-3 sm:py-3 bg-white text-gray-900 border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm sm:text-base font-tajawal placeholder-gray-400' . ($errors->has($name) ? ' border-red-500' : '')
        ]) }}
    >

    @error($name)
        <p class="mt-2 text-right text-sm text-red-300 font-bold">{{ $message }}</p>
    @enderror
</div>
