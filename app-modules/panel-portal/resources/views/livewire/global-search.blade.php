<div class="relative flex-1 max-w-[520px]" x-data x-on:click.outside="$wire.q = ''">
    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="absolute left-[13px] top-1/2 -translate-y-1/2 text-faint pointer-events-none"><circle cx="11" cy="11" r="7"></circle><path d="M21 21l-4.35-4.35"></path></svg>
    <input
        type="text"
        wire:model.live.debounce.250ms="q"
        placeholder="{{ __('panel-portal::portal.search.placeholder') }}"
        class="w-full bg-surface border border-white/9 rounded-[9px] py-2 pr-[14px] pl-[38px] text-ink text-[13px] font-sans outline-none focus:border-accent/55 focus:shadow-[0_0_0_3px_rgba(164,143,250,.12)]"
    >
    @if (trim($q) !== '')
        <div class="absolute top-11 inset-x-0 bg-panel border border-white/10 rounded-xl shadow-[0_18px_50px_rgba(0,0,0,.55)] overflow-hidden animate-pop z-50">
            @forelse ($results as $result)
                <a href="{{ $result['url'] }}" wire:key="result-{{ $result['qid'] }}" class="flex items-center gap-3 w-full px-[14px] py-[10px] border-b border-white/5 hover:bg-accent/8">
                    <span class="flex-none w-7 h-7 rounded-[7px] bg-accent-deep/10 grid place-items-center text-accent">
                        <x-panel-portal::format-icon :format="$result['format']" :size="14" />
                    </span>
                    <span class="min-w-0 flex-1">
                        <span class="block text-[13px] font-semibold text-ink whitespace-nowrap overflow-hidden text-ellipsis">{{ $result['title'] }}</span>
                        <span class="block text-[10.5px] font-mono text-faint mt-px">{{ $result['qid'] }}</span>
                    </span>
                    <span class="flex-none text-[10.5px] font-mono text-muted bg-white/5 px-2 py-0.5 rounded-[5px]">{{ $result['hint'] }}</span>
                </a>
            @empty
                <div class="px-[14px] py-[18px] text-[12.5px] text-faint text-center">{{ __('panel-portal::portal.search.empty', ['query' => trim($q)]) }}</div>
            @endforelse
        </div>
    @endif
</div>
