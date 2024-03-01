<x-dynamic-component
	:component="$getFieldWrapperView()"
	:field="$field"
>
	<div x-data="{ state: $wire.{{ $applyStateBindingModifiers("\$entangle('{$getStatePath()}')") }} }">
		{{ $getChildComponentContainer() }}
	</div>
</x-dynamic-component>
