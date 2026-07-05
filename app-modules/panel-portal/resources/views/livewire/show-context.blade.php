@php use He4rt\Portal\Support\ContextType; @endphp
<div class="grid grid-cols-[268px_minmax(0,1fr)_236px] max-w-[1440px] mx-auto">
    <x-panel-portal::sidebar :context="$context" :groups="$groups" />

    <div class="min-w-0">
        <main class="pt-9 px-12 pb-20 max-w-[820px] animate-fade-up">
            <div class="text-[10.5px] tracking-[.09em] uppercase text-accent font-mono mb-[10px]">{{ $context->typeLabel() }}</div>
            <h1 class="mb-3 text-[32px] font-bold tracking-[-.025em] leading-[1.15]">{{ $context->name() }}</h1>
            <p class="mb-[22px] text-[15.5px] text-muted leading-[1.6] max-w-[600px]">{{ $subtitle }}</p>

            @if ($context->type === ContextType::Project && $context->project?->repo_url !== null)
                <div class="flex items-center gap-[14px] flex-wrap mb-[30px] px-4 py-[14px] bg-surface border border-white/7 rounded-xl">
                    <span class="inline-flex items-center gap-2 text-[12px] font-mono text-soft">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="text-muted"><path d="M12 2a10 10 0 00-3.16 19.5c.5.09.68-.22.68-.48v-1.7c-2.78.6-3.37-1.34-3.37-1.34-.45-1.16-1.11-1.47-1.11-1.47-.9-.62.07-.6.07-.6 1 .07 1.53 1.03 1.53 1.03.9 1.52 2.34 1.08 2.91.83.09-.65.35-1.09.63-1.34-2.22-.25-4.56-1.11-4.56-4.95 0-1.09.39-1.98 1.03-2.68-.1-.25-.45-1.27.1-2.64 0 0 .84-.27 2.75 1.02a9.58 9.58 0 015 0c1.91-1.29 2.75-1.02 2.75-1.02.55 1.37.2 2.39.1 2.64.64.7 1.03 1.59 1.03 2.68 0 3.85-2.34 4.7-4.57 4.95.36.31.68.92.68 1.85V21c0 .27.18.58.69.48A10 10 0 0012 2z"></path></svg>
                        {{ str_replace(['https://', 'http://'], '', $context->project->repo_url) }}
                    </span>
                    <span class="text-[11px] text-faint font-mono">{{ __('panel-portal::portal.overview.branch', ['branch' => $context->project->default_branch ?? 'main']) }}</span>
                    @if ($context->project->last_synced_at !== null)
                        <span class="ml-auto inline-flex items-center gap-1.5 text-[11px] text-mirror-soft font-mono"><span class="w-1.5 h-1.5 rounded-full bg-mirror inline-block"></span>{{ __('panel-portal::portal.overview.mirrored_via_federation') }}</span>
                    @endif
                </div>
            @endif

            @if ($introHtml !== null)
                <div class="portal-prose-sm mb-[30px]">{!! $introHtml !!}</div>
            @endif

            <x-panel-portal::section-label :label="$listLabel" class="mb-[14px]" />

            <div class="flex flex-col gap-[10px]">
                @foreach ($flat as $item)
                    <a href="{{ $context->entryUrl($item) }}" wire:key="ov-{{ $item->id }}" class="flex items-center gap-[14px] px-4 py-[14px] bg-surface border border-white/7 rounded-xl transition-colors hover:border-accent/45">
                        @if ($context->type === ContextType::Collection)
                            <span class="flex-none w-[30px] h-[30px] rounded-full bg-[#1C1B24] border-2 border-accent/35 text-accent grid place-items-center text-[11px] font-bold font-mono">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                        @endif
                        <span class="flex-none w-[34px] h-[34px] rounded-[9px] bg-accent-deep/10 grid place-items-center text-accent">
                            <x-panel-portal::format-icon :format="$item->format" :size="16" />
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="block text-[14.5px] font-semibold text-ink">{{ $item->title }}</span>
                            <span class="block text-[12.5px] text-muted mt-0.5 overflow-hidden text-ellipsis whitespace-nowrap">{{ $item->summary }}</span>
                        </span>
                        <x-panel-portal::badge.purpose :purpose="$item->purpose" class="flex-none" />
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" class="flex-none text-faint"><path d="M9 5l7 7-7 7"></path></svg>
                    </a>
                @endforeach
            </div>

            @if ($context->type === ContextType::Area && $trails->isNotEmpty())
                <x-panel-portal::section-label :label="__('panel-portal::portal.overview.area_trails')" class="mt-8 mb-[14px]" />
                <div class="grid grid-cols-2 gap-3">
                    @foreach ($trails as $trail)
                        <a href="{{ route('portal.collections.show', ['collection' => $trail]) }}" wire:key="trail-{{ $trail->id }}" class="bg-surface border border-white/7 rounded-xl p-4 transition-colors hover:border-accent/45">
                            <div class="flex items-center gap-2 mb-2">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" class="text-accent"><path d="M4 6h16"></path><path d="M4 12h16"></path><path d="M4 18h10"></path></svg>
                                <span class="text-[10.5px] font-mono text-faint">{{ __('panel-portal::portal.overview.trail_meta', ['count' => $trail->entries_count]) }}</span>
                            </div>
                            <div class="text-[14.5px] font-bold tracking-[-.01em] mb-[5px]">{{ $trail->title }}</div>
                            <div class="text-[12.5px] text-muted leading-[1.55]">{{ $trail->summary }}</div>
                        </a>
                    @endforeach
                </div>
            @endif
        </main>
    </div>

    <aside class="sticky top-14 h-[calc(100vh-56px)] overflow-y-auto pt-9 pr-5 pb-10 pl-2"></aside>
</div>
