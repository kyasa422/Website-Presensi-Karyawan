@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-medium text-sm text-gray-950 dark:text-gray-900']) }}>
    {{ $value ?? $slot }}
</label>
