@props(['origin'])
@php
    $classes = match ($origin) {
        \He4rt\Catalog\Enums\Origin::Native => 'text-accent bg-accent-deep/14',
        \He4rt\Catalog\Enums\Origin::Mirror => 'text-mirror bg-mirror/12',
    };
@endphp
<span {{ $attributes->merge(['class' => 'text-[11px] font-semibold font-mono px-2 py-[3px] rounded-[5px] '.$classes]) }}>{{ $origin->getLabel() }}</span>
