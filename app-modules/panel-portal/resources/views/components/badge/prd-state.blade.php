@props(['state'])
@php
    $classes = match ($state) {
        \He4rt\Catalog\Enums\PrdVersionState::Draft => 'text-muted bg-muted/12',
        \He4rt\Catalog\Enums\PrdVersionState::Frozen => 'text-info bg-info/12',
    };
@endphp
<span {{ $attributes->merge(['class' => 'text-[10px] font-semibold font-mono px-[7px] py-[2px] rounded '.$classes]) }}>{{ $state->getLabel() }}</span>
