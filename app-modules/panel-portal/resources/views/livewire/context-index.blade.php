<main class="max-w-[1060px] mx-auto px-8 pt-10 pb-20 animate-fade-up">
    <div class="text-[10.5px] tracking-[.09em] uppercase text-accent font-mono mb-[10px]">{{ __('panel-portal::portal.index.kicker') }}</div>
    <h1 class="mb-[10px] text-[30px] font-bold tracking-[-.025em]">{{ $title }}</h1>
    <p class="mb-[30px] text-[15px] text-muted max-w-[640px] leading-[1.6]">{{ $subtitle }}</p>

    <div class="grid grid-cols-[repeat(auto-fill,minmax(300px,1fr))] gap-[14px]">
        @foreach ($cards as $card)
            <a href="{{ $card['url'] }}" wire:key="card-{{ $loop->index }}" class="flex flex-col gap-[10px] bg-surface border border-white/7 rounded-[14px] p-5 transition-colors hover:border-accent/45">
                <div class="flex items-center gap-[10px]">
                    <span class="flex-none w-[34px] h-[34px] rounded-[9px] bg-accent-deep/14 grid place-items-center font-mono text-[11px] font-bold text-accent">{{ $card['badge'] }}</span>
                    <span class="min-w-0 flex-1 text-[15.5px] font-bold tracking-[-.01em] text-ink">{{ $card['title'] }}</span>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" class="flex-none text-faint"><path d="M9 5l7 7-7 7"></path></svg>
                </div>
                <div class="text-[13px] text-muted leading-[1.55] min-h-10">{{ $card['description'] }}</div>
                <div class="flex items-center gap-1.5 flex-wrap mt-auto">
                    <span class="text-[11px] font-mono text-faint">{{ $card['meta'] }}</span>
                    <span class="flex-1"></span>
                    @foreach ($card['chips'] as $chip)
                        <span @class([
                            'text-[10.5px] font-semibold font-mono px-2 py-[2px] rounded-full',
                            'text-mirror-soft bg-mirror/12' => $chip['style'] === 'mirror',
                            'text-accent bg-accent-deep/14' => $chip['style'] === 'accent',
                            'text-soft bg-white/6' => $chip['style'] === 'neutral',
                        ])>{{ $chip['label'] }}</span>
                    @endforeach
                </div>
            </a>
        @endforeach
    </div>
</main>
