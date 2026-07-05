@props(['status'])
@php
    [$text, $dot] = match ($status) {
        \He4rt\Catalog\Enums\Status::Draft => ['text-muted', 'bg-muted'],
        \He4rt\Catalog\Enums\Status::Review => ['text-warn', 'bg-warn'],
        \He4rt\Catalog\Enums\Status::Published => ['text-ok', 'bg-ok'],
        \He4rt\Catalog\Enums\Status::Obsolete => ['text-bad', 'bg-bad'],
    };
@endphp
<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 text-[12px] '.$text]) }}><span class="w-[7px] h-[7px] rounded-full inline-block {{ $dot }}"></span>{{ $status->getLabel() }}</span>
