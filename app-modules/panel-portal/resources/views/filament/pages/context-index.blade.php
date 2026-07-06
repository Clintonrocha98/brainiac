<x-filament-panels::page>
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @foreach ($cards as $card)
            <a
                href="{{ $card->url }}"
                wire:key="card-{{ $loop->index }}"
                class="flex flex-col gap-3 rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-950/5 transition hover:ring-primary-600 dark:bg-gray-900 dark:ring-white/10 dark:hover:ring-primary-500"
            >
                <div class="flex items-center gap-3">
                    <span class="grid size-9 flex-none place-items-center rounded-lg bg-primary-50 font-mono text-xs font-bold text-primary-600 dark:bg-primary-500/10 dark:text-primary-400">{{ $card->badge }}</span>
                    <span class="min-w-0 flex-1 truncate text-sm font-semibold text-gray-950 dark:text-white">{{ $card->title }}</span>
                    <x-filament::icon icon="heroicon-m-chevron-right" class="size-4 flex-none text-gray-400 dark:text-gray-500" />
                </div>
                <p class="min-h-10 text-sm text-gray-500 dark:text-gray-400">{{ $card->description }}</p>
                <div class="mt-auto flex flex-wrap items-center gap-2">
                    <span class="font-mono text-xs text-gray-400 dark:text-gray-500">{{ $card->meta }}</span>
                    <span class="flex-1"></span>
                    @foreach ($card->chips as $chip)
                        <x-filament::badge :color="$chip->color" size="sm">{{ $chip->label }}</x-filament::badge>
                    @endforeach
                </div>
            </a>
        @endforeach
    </div>
</x-filament-panels::page>
