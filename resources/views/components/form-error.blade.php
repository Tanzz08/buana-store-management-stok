@props([
    'field',
    'id' => null,
])

@if ($errors->has($field))
    <p id="{{ $id ?? $field . '-error' }}"
       class="mt-1 text-sm text-red-600"
       role="alert"
       aria-live="polite">{{ $errors->first($field) }}</p>
@endif
