@props(['purpose'])
@php
    $classes = match ($purpose) {
        \He4rt\Catalog\Enums\Purpose::Reference => 'text-info bg-info/12',
        \He4rt\Catalog\Enums\Purpose::HowTo => 'text-ok bg-ok/12',
        \He4rt\Catalog\Enums\Purpose::Explanation => 'text-muted bg-muted/12',
    };
@endphp
<span {{ $attributes->merge(['class' => 'text-[11px] font-semibold font-mono px-2 py-[3px] rounded-[5px] '.$classes]) }}>{{ $purpose->getLabel() }}</span>
