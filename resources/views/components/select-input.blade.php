@props([
    'disabled' => false,
    'options' => [], // array of value => label
    'selected' => null,
    'id' => '',
    'name' => '',
    'required' => false,
    'placeholder' => 'Select an option',
])

<select {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm']) !!} id="{{ $id ?? $name }}" name="{{ $name }}" {{ $required ? 'required' : '' }}>
    @if($placeholder)
        <option value="" {{ is_null($selected) || $selected === '' ? 'selected' : '' }} disabled>{{ $placeholder }}</option>
    @endif
    @foreach($options as $value => $label)
        <option value="{{ $value }}" {{ (string)$selected === (string)$value ? 'selected' : '' }}>
            {{ $label }}
        </option>
    @endforeach
</select>

@error($name)
    <p class="text-sm text-red-600 dark:text-red-400 mt-2">{{ $message }}</p>
@enderror
