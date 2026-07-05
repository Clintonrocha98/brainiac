@php use He4rt\Catalog\Enums\Origin; @endphp
<div class="grid grid-cols-[268px_minmax(0,1fr)_236px] max-w-[1440px] mx-auto">
    <x-panel-portal::sidebar :context="$context" :groups="$groups" :active-entry-id="$entry->id" />

    <div class="min-w-0">
        <main class="pt-9 px-12 pb-20 max-w-[820px] animate-fade-up">
            {{-- breadcrumb --}}
            <div class="flex items-center gap-2 text-[12px] text-faint mb-[18px]">
                <a href="{{ $context->overviewUrl() }}" class="text-muted hover:text-accent">{{ $context->name() }}</a>
                <span class="text-divider">/</span>
                <span class="text-faint">{{ $groupLabel }}</span>
                <span class="text-divider">/</span>
                <span class="text-soft">{{ $entry->title }}</span>
            </div>

            {{-- badges + versão PRD --}}
            <div class="flex items-center gap-2 mb-[14px] flex-wrap">
                <span class="text-[11px] font-semibold font-mono text-soft bg-white/6 px-[9px] py-[3px] rounded-[5px]">{{ $entry->format->getLabel() }}</span>
                <x-panel-portal::badge.purpose :purpose="$entry->purpose" />
                <x-panel-portal::badge.origin :origin="$entry->origin" />
                <x-panel-portal::badge.status :status="$entry->status" class="ml-0.5" />

                @if ($versions->isNotEmpty())
                    <span class="relative ml-auto" x-data="{ open: false }">
                        <button type="button" x-on:click="open = ! open" class="inline-flex items-center gap-2 px-[11px] py-[5px] bg-surface border border-white/10 rounded-lg cursor-pointer hover:border-accent/45">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-accent"><path d="M7 7h.01M3 12V5a2 2 0 012-2h7l9 9-9 9-9-9z"></path></svg>
                            <span class="text-[12px] font-bold font-mono text-ink">{{ $selectedVersionLabel }}</span>
                            <x-panel-portal::badge.prd-state :state="$selectedVersion->state" />
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" class="text-faint"><path d="M6 9l6 6 6-6"></path></svg>
                        </button>
                        <span x-cloak x-show="open" x-on:click.outside="open = false" class="absolute top-[38px] right-0 z-45 block min-w-[250px] bg-panel border border-white/10 rounded-xl shadow-[0_18px_50px_rgba(0,0,0,.55)] p-1.5 animate-pop">
                            <span class="block text-[9.5px] tracking-[.09em] uppercase text-faint font-mono px-[10px] pt-1.5 pb-[5px]">{{ __('panel-portal::portal.prd.versions') }}</span>
                            @foreach ($versionOptions as $option)
                                <button
                                    type="button"
                                    wire:key="ver-{{ $option['value'] }}"
                                    wire:click="selectVersion('{{ $option['value'] }}')"
                                    x-on:click="open = false"
                                    @class([
                                        'flex items-center gap-[9px] w-full px-[10px] py-[7px] rounded-lg cursor-pointer hover:bg-accent/10',
                                        'bg-accent-deep/12' => $option['selected'],
                                    ])
                                >
                                    <span @class(['flex-none text-[12px] font-bold font-mono', 'text-ink' => $option['selected'], 'text-soft' => ! $option['selected']])>{{ $option['label'] }}</span>
                                    <x-panel-portal::badge.prd-state :state="$option['state']" class="flex-none" />
                                    <span class="ml-auto text-[10px] text-faint font-mono">{{ $option['meta'] }}</span>
                                </button>
                            @endforeach
                        </span>
                    </span>
                @endif
            </div>

            <h1 class="mb-[10px] text-[30px] font-bold tracking-[-.025em] leading-[1.2]">{{ $entry->title }}</h1>
            <p class="mb-[10px] text-[15.5px] leading-[1.55] text-muted">{{ $entry->summary }}</p>
            <div class="flex items-center gap-[10px] text-[11.5px] text-faint font-mono mb-[26px] pb-5 border-b border-white/7 flex-wrap">
                <span>{{ $entry->qualified_id }}</span><span>·</span><span>{{ __('panel-portal::portal.reader.updated', ['date' => $updatedAt]) }}</span><span>·</span><span>{{ $byLine }}</span>
            </div>

            {{-- banner: lendo versão antiga do PRD --}}
            @if ($showOldVersionBanner)
                <div class="flex items-center gap-[10px] px-[14px] py-[10px] border border-warn/30 bg-warn/7 rounded-[9px] mb-[26px] text-[12.5px] text-warn-soft">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" class="text-warn"><circle cx="12" cy="12" r="9"></circle><path d="M12 7v5l3 3"></path></svg>
                    <span class="flex-1">{!! __('panel-portal::portal.prd.old_version_banner', ['version' => '<strong class="font-mono text-warn-strong font-bold">'.e($selectedVersionLabel).'</strong>', 'date' => e($selectedVersion->frozen_at?->timezone(config('app.display_timezone'))->translatedFormat('d M Y') ?? '')]) !!}</span>
                    <button type="button" wire:click="selectVersion(null)" class="text-[12px] font-semibold text-warn-strong border border-warn/40 px-[11px] py-1 rounded-full cursor-pointer hover:bg-warn/12">{{ __('panel-portal::portal.prd.view_latest', ['version' => $latestVersionLabel]) }}</button>
                </div>
            @endif

            {{-- banner: espelho read-only --}}
            @if ($entry->origin === Origin::Mirror)
                <div class="flex items-center gap-[10px] px-[14px] py-[10px] border border-mirror/25 bg-mirror/7 rounded-[9px] mb-[26px] text-[12.5px] text-mirror-soft">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" class="text-mirror"><rect x="5" y="11" width="14" height="9" rx="2"></rect><path d="M8 11V8a4 4 0 018 0v3"></path></svg>
                    {{ __('panel-portal::portal.reader.mirror_banner') }}
                </div>
            @endif

            {{-- corpo --}}
            @if ($bodyHtml !== null)
                <div class="portal-prose">{!! $bodyHtml !!}</div>
            @else
                <div class="px-6 py-[52px] text-center border border-dashed border-white/14 rounded-xl text-faint">
                    <div class="text-[15px] font-semibold text-muted mb-1.5">{{ __('panel-portal::portal.reader.no_body_title') }}</div>
                    <div class="text-[13px]">{{ __('panel-portal::portal.reader.no_body_text') }}</div>
                </div>
            @endif

            {{-- ligações --}}
            @if (count($links) > 0)
                <div class="mt-[38px]">
                    <x-panel-portal::section-label :label="__('panel-portal::portal.reader.links')" class="mb-3" />
                    <div class="grid grid-cols-2 gap-[10px]">
                        @foreach ($links as $link)
                            <a href="{{ $link['url'] }}" wire:key="link-{{ $loop->index }}" class="block px-[14px] py-3 rounded-[10px] bg-surface border border-white/7 hover:border-accent/45">
                                <span class="block text-[10px] tracking-[.07em] uppercase font-mono text-accent mb-1">{{ $link['label'] }}</span>
                                <span class="block text-[13.5px] font-semibold text-ink">{{ $link['title'] }}</span>
                                <span class="block text-[10.5px] font-mono text-faint mt-0.5">{{ $link['qid'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- artefatos --}}
            @if ($entry->artifacts->isNotEmpty())
                <div class="mt-[26px]">
                    <x-panel-portal::section-label :label="__('panel-portal::portal.reader.artifacts')" class="mb-[10px]" />
                    <div class="flex flex-col gap-1.5">
                        @foreach ($entry->artifacts as $artifact)
                            <a href="{{ $artifact->url }}" target="_blank" rel="noopener" wire:key="artifact-{{ $artifact->id }}" class="inline-flex items-center gap-2 text-[12.5px] text-accent break-all hover:underline">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="flex-none"><path d="M10 13a5 5 0 007.5.5l3-3a5 5 0 00-7-7l-1.5 1.5"></path><path d="M14 11a5 5 0 00-7.5-.5l-3 3a5 5 0 007 7L12 19"></path></svg>{{ $artifact->url }}
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- anterior / próximo --}}
            <div class="grid grid-cols-2 gap-3 mt-11 pt-[22px] border-t border-white/7">
                @if ($previous !== null)
                    <a href="{{ $context->entryUrl($previous) }}" class="block px-4 py-[14px] rounded-[11px] border border-white/8 text-left hover:border-accent/45 hover:bg-accent/5">
                        <span class="block text-[10px] tracking-[.08em] uppercase font-mono text-faint mb-1">{{ __('panel-portal::portal.reader.previous') }}</span>
                        <span class="block text-[13.5px] font-semibold text-soft">{{ $previous->title }}</span>
                    </a>
                @else
                    <span></span>
                @endif
                @if ($next !== null)
                    <a href="{{ $context->entryUrl($next) }}" class="block px-4 py-[14px] rounded-[11px] border border-white/8 text-right hover:border-accent/45 hover:bg-accent/5">
                        <span class="block text-[10px] tracking-[.08em] uppercase font-mono text-faint mb-1">{{ __('panel-portal::portal.reader.next') }}</span>
                        <span class="block text-[13.5px] font-semibold text-soft">{{ $next->title }}</span>
                    </a>
                @endif
            </div>
        </main>
    </div>

    {{-- rail direito --}}
    <aside class="sticky top-14 h-[calc(100vh-56px)] overflow-y-auto pt-9 pr-5 pb-10 pl-2">
        @if (count($toc) > 1)
            <div class="mb-6">
                <div class="text-[10px] tracking-[.09em] uppercase text-faint font-mono mb-[10px]">{{ __('panel-portal::portal.reader.on_this_page') }}</div>
                <div class="flex flex-col gap-0.5 border-l border-white/8">
                    @foreach ($toc as $heading)
                        <a href="#{{ $heading['slug'] }}" class="block py-1 pl-3 text-[12.5px] text-muted border-l-2 border-transparent -ml-px hover:text-accent hover:border-accent/60">{{ $heading['text'] }}</a>
                    @endforeach
                </div>
            </div>
        @endif

        @if ($versions->isNotEmpty())
            <div class="bg-surface border border-white/7 rounded-xl pt-4 px-[14px] pb-2.5 mb-4">
                <div class="flex items-center gap-2 text-[10px] tracking-[.09em] uppercase text-faint font-mono mb-3">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-accent"><path d="M7 7h.01M3 12V5a2 2 0 012-2h7l9 9-9 9-9-9z"></path></svg>
                    {{ __('panel-portal::portal.prd.versions') }}
                </div>
                <div class="relative flex flex-col gap-0.5">
                    <div class="absolute left-2 top-3 bottom-3 w-0.5 bg-accent/16"></div>
                    @foreach ($versionOptions as $option)
                        <button
                            type="button"
                            wire:key="rail-ver-{{ $option['value'] }}"
                            wire:click="selectVersion('{{ $option['value'] }}')"
                            @class([
                                'relative flex items-center gap-[10px] py-[7px] pr-2 pl-0.5 rounded-lg cursor-pointer hover:bg-accent/10',
                                'bg-accent-deep/12' => $option['selected'],
                            ])
                        >
                            <span @class(['flex-none w-[14px] h-[14px] rounded-full bg-surface border-2 inline-block relative z-1', 'border-accent' => $option['selected'], 'border-accent/35' => ! $option['selected']])></span>
                            <span @class(['flex-none text-[12px] font-bold font-mono', 'text-ink' => $option['selected'], 'text-soft' => ! $option['selected']])>{{ $option['label'] }}</span>
                            <x-panel-portal::badge.prd-state :state="$option['state']" class="flex-none text-[9.5px] px-1.5" />
                            <span class="ml-auto text-[9.5px] text-faint font-mono text-right">{{ $option['meta'] }}</span>
                        </button>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="bg-surface border border-white/7 rounded-xl pt-4 px-4 pb-3 mb-4">
            <div class="text-[10px] tracking-[.09em] uppercase text-faint font-mono mb-3">{{ __('panel-portal::portal.reader.about') }}</div>
            <div class="grid grid-cols-[74px_1fr] gap-y-[9px] text-[12px] items-center">
                <span class="text-faint">{{ __('panel-portal::portal.reader.department') }}</span><span class="text-soft">{{ $entry->department->getLabel() }}</span>
                <span class="text-faint">{{ __('panel-portal::portal.reader.audience') }}</span><span class="text-soft">{{ $audienceText }}</span>
                <span class="text-faint">{{ $authorshipLabel }}</span><span class="text-soft">{{ $authorsText }}</span>
                <span class="text-faint">{{ __('panel-portal::portal.reader.status') }}</span><x-panel-portal::badge.status :status="$entry->status" />
            </div>
            @if (($entry->keywords ?? []) !== [])
                <div class="flex flex-wrap gap-[5px] mt-3 mb-1">
                    @foreach ($entry->keywords as $keyword)
                        <span class="text-[10.5px] font-mono text-muted bg-white/4 px-[7px] py-0.5 rounded">{{ $keyword }}</span>
                    @endforeach
                </div>
            @endif
        </div>

        @if ($sourceUrl !== null)
            <a href="{{ $sourceUrl }}" target="_blank" rel="noopener" class="flex items-center justify-center gap-2 px-[14px] py-[9px] rounded-[9px] bg-accent-deep/14 border border-accent/40 text-accent-soft text-[12.5px] font-semibold hover:bg-accent-deep/24">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M14 4h6v6"></path><path d="M20 4L10 14"></path><path d="M20 14v5a1 1 0 01-1 1H5a1 1 0 01-1-1V5a1 1 0 011-1h5"></path></svg>
                {{ __('panel-portal::portal.reader.view_source') }}
            </a>
            <div class="text-[10px] font-mono text-faint mt-[7px] break-all text-center">{{ $entry->document?->git_pointer }}</div>
        @endif
    </aside>
</div>
