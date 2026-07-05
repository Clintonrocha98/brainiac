@props(['context', 'groups', 'activeEntryId' => null])
<aside class="sticky top-14 h-[calc(100vh-56px)] overflow-y-auto pt-5 px-4 pb-10 border-r border-white/6">
    <a href="{{ $context->indexUrl() }}" class="inline-flex items-center gap-1.5 text-[11.5px] text-muted px-[10px] py-0.5 mb-[10px] hover:text-accent">
        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M15 5l-7 7 7 7"></path></svg>
        {{ $context->backLabel() }}
    </a>

    <div class="mb-[18px] p-3 bg-surface border border-white/8 rounded-xl">
        <div class="flex items-center gap-[10px]">
            <span class="flex-none w-[30px] h-[30px] rounded-lg bg-accent-deep/14 grid place-items-center font-mono text-[10px] font-bold text-accent">{{ $context->badge() }}</span>
            <span class="min-w-0 flex-1">
                <span class="block text-[9.5px] tracking-[.08em] uppercase text-faint font-mono">{{ $context->typeLabel() }}</span>
                <span class="block text-[13.5px] font-semibold text-ink truncate">{{ $context->name() }}</span>
            </span>
        </div>
    </div>

    <nav>
        <a
            href="{{ $context->overviewUrl() }}"
            @class([
                'flex items-center gap-2 w-full px-[10px] py-[5px] rounded-[7px] text-[12.5px] mb-2 hover:text-ink',
                'font-semibold text-ink bg-accent-deep/12' => $activeEntryId === null,
                'font-normal text-muted' => $activeEntryId !== null,
            ])
        >
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 11l9-8 9 8"></path><path d="M5 9.5V21h14V9.5"></path></svg>
            {{ __('panel-portal::portal.context.overview') }}
        </a>

        @foreach ($groups as $group)
            <div class="mb-3">
                <x-panel-portal::section-label :label="$group['label']" class="text-[9.5px] px-[10px] mb-1" />
                <div class="flex flex-col gap-px">
                    @foreach ($group['entries'] as $item)
                        <a
                            href="{{ $context->entryUrl($item) }}"
                            wire:key="nav-{{ $item->id }}"
                            @class([
                                'flex items-center gap-[9px] w-full px-[10px] py-[5px] rounded-[7px] text-[12.5px] border-l-2 hover:text-ink hover:bg-accent/7',
                                'font-semibold text-ink bg-accent-deep/12 border-accent' => $activeEntryId === $item->id,
                                'font-normal text-muted border-transparent' => $activeEntryId !== $item->id,
                            ])
                        >
                            @if ($group['positioned'])
                                <span class="flex-none font-mono text-[10px] text-accent font-bold">{{ str_pad((string) ($loop->iteration), 2, '0', STR_PAD_LEFT) }}</span>
                            @endif
                            <span class="min-w-0 flex-1 truncate">{{ $item->title }}</span>
                            @if ($item->origin === \He4rt\Catalog\Enums\Origin::Mirror)
                                <span title="{{ __('panel-portal::portal.context.mirror_hint') }}" class="flex-none w-1.5 h-1.5 rounded-full bg-mirror inline-block"></span>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>
        @endforeach
    </nav>
</aside>
