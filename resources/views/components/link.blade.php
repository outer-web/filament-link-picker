@php
	$attributes = $attributes->merge([
	    'href' => $link->build($absolute, $locale),
	    'download' => $attributes->get('download') ?? $link->is_download,
	    'target' => $attributes->get('target') ?? $link->opens_in_new_tab ? '_blank' : '_self',
	]);
@endphp

<a {{ $attributes }}>
	{{ $label ?? $slot }}
</a>
