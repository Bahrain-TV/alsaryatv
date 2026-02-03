@props([
    'name',
    'checked' => false
])

<div>
    <label for="{{ $name }}" class="inline-flex items-center">
        <input
            type="checkbox"
            id="{{ $name }}"
            name="{{ $name }}"
            {{ $checked ? 'checked' : '' }}
            {{ $attributes->merge([
                'class' => 'form-checkbox h-5 w-5 text-indigo-600 transition duration-150 ease-in-out'
            ]) }}
        >
        <span class="ml-2 text-lg font-medium font-tajawal text-gray-700">
            {{ $slot }}
        </span>
    </label>

    @error($name)
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
