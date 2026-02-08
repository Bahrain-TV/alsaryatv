@props([
    'name',
    'label',
    'type' => 'text',
    'value' => '',
    'placeholder' => ''
])

<div class="mt-5">
    <label for="{{ $name }}" class="block text-right text-base sm:text-lg font-bold text-amber-200 mb-4 drop-shadow-lg">
        {{ $label }}
    </label>

    <input
        type="{{ $type }}"
        id="{{ $name }}"
        name="{{ $name }}"
        value="{{ $value }}"
        placeholder="{{ $placeholder }}"
        {{ $attributes->merge([
            'class' => 'w-full px-4 py-3 sm:py-3 bg-slate-900/70 text-slate-100 border-2 border-amber-300/40 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-amber-400 text-sm sm:text-base font-tajawal placeholder-slate-400' . ($errors->has($name) ? ' border-red-400' : '')
        ]) }}
    >

    @error($name)
        <p class="mt-2 text-right text-sm text-red-300 font-bold">{{ $message }}</p>
    @enderror
</div>
