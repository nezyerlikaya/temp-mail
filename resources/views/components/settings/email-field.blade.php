@props(['name', 'label', 'value', 'required' => false, 'help' => null])

<x-form.input :name="$name" :label="$label" type="email" :value="$value" autocomplete="email" inputmode="email" :help="$help" :required="$required" />
