@php use He4rt\Portal\Support\ContextType; @endphp
<x-filament-panels::page>
    @if ($context->type === ContextType::Project && $context->project?->repo_url !== null)
        <x-filament::section compact>
            <div class="flex flex-wrap items-center gap-3">
                <x-filament::icon icon="heroicon-o-code-bracket" class="size-4 flex-none text-gray-400 dark:text-gray-500" />
                <span class="font-mono text-xs text-gray-600 dark:text-gray-300">{{ str_replace(['https://', 'http://'], '', $context->project->repo_url) }}</span>
                <span class="font-mono text-xs text-gray-400 dark:text-gray-500">{{ __('panel-portal::portal.overview.branch', ['branch' => $context->project->default_branch ?? 'main']) }}</span>
                @if ($context->project->last_synced_at !== null)
                    <x-filament::badge color="info" size="sm" class="ms-auto">{{ __('panel-portal::portal.overview.mirrored_via_federation') }}</x-filament::badge>
                @endif
            </div>
        </x-filament::section>
    @endif

    @if ($introHtml !== null)
        <div class="prose prose-sm max-w-none dark:prose-invert">{!! $introHtml !!}</div>
    @endif

    <x-filament::section>
        <x-slot name="heading">{{ $listLabel }}</x-slot>

        <div class="flex flex-col gap-2">
            @foreach ($flat as $item)
                <a
                    href="{{ $context->entryUrl($item) }}"
                    wire:key="overview-{{ $item->id }}"
                    class="flex items-center gap-4 rounded-lg px-3 py-3 ring-1 ring-gray-950/5 transition hover:bg-gray-50 dark:ring-white/10 dark:hover:bg-white/5"
                >
                    @if ($context->type === ContextType::Collection)
                        <span class="grid size-7 flex-none place-items-center rounded-full font-mono text-xs font-bold text-primary-600 ring-1 ring-primary-600/40 dark:text-primary-400 dark:ring-primary-400/40">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                    @endif
                    <x-filament::icon :icon="$item->format->getIcon()" class="size-5 flex-none text-primary-600 dark:text-primary-400" />
                    <span class="min-w-0 flex-1">
                        <span class="block truncate text-sm font-semibold text-gray-950 dark:text-white">{{ $item->title }}</span>
                        <span class="block truncate text-xs text-gray-500 dark:text-gray-400">{{ $item->summary }}</span>
                    </span>
                    <x-filament::badge :color="$item->purpose->getColor()" size="sm" class="hidden flex-none sm:inline-flex">{{ $item->purpose->getLabel() }}</x-filament::badge>
                    <x-filament::icon icon="heroicon-m-chevron-right" class="size-4 flex-none text-gray-400 dark:text-gray-500" />
                </a>
            @endforeach
        </div>
    </x-filament::section>

    @if ($trailCards !== [])
        <x-filament::section>
            <x-slot name="heading">{{ __('panel-portal::portal.overview.area_trails') }}</x-slot>

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                @foreach ($trailCards as $trailCard)
                    <a
                        href="{{ $trailCard->url }}"
                        wire:key="trail-{{ $loop->index }}"
                        class="flex flex-col gap-2 rounded-lg p-4 ring-1 ring-gray-950/5 transition hover:bg-gray-50 dark:ring-white/10 dark:hover:bg-white/5"
                    >
                        <div class="flex items-center gap-2">
                            <x-filament::icon icon="heroicon-o-queue-list" class="size-4 text-primary-600 dark:text-primary-400" />
                            <span class="font-mono text-xs text-gray-400 dark:text-gray-500">{{ $trailCard->meta }}</span>
                        </div>
                        <span class="text-sm font-semibold text-gray-950 dark:text-white">{{ $trailCard->title }}</span>
                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ $trailCard->description }}</span>
                    </a>
                @endforeach
            </div>
        </x-filament::section>
    @endif
</x-filament-panels::page>
