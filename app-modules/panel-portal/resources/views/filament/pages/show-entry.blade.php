@php use He4rt\Catalog\Enums\Origin; @endphp
<x-filament-panels::page>
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,1fr)_18rem]">
        <div class="min-w-0 space-y-6">
            {{-- badges de faceta + seletor de versão do PRD --}}
            <div class="flex flex-wrap items-center gap-2">
                <x-filament::badge color="gray" size="sm">{{ $entry->format->getLabel() }}</x-filament::badge>
                <x-filament::badge :color="$entry->purpose->getColor()" size="sm">{{ $entry->purpose->getLabel() }}</x-filament::badge>
                <x-filament::badge :color="$entry->origin->getColor()" size="sm">{{ $entry->origin->getLabel() }}</x-filament::badge>
                <x-filament::badge :color="$entry->status->getColor()" size="sm">{{ $entry->status->getLabel() }}</x-filament::badge>

                @if (! $versionStack->isEmpty())
                    <div class="ms-auto">
                        <x-filament::dropdown placement="bottom-end">
                            <x-slot name="trigger">
                                <x-filament::button color="gray" size="sm" icon="heroicon-m-tag" icon-position="before">
                                    {{ $versionStack->selectedLabel() }}
                                </x-filament::button>
                            </x-slot>

                            <x-filament::dropdown.header>{{ __('panel-portal::portal.prd.versions') }}</x-filament::dropdown.header>

                            <x-filament::dropdown.list>
                                @foreach ($versionStack->options() as $option)
                                    <x-filament::dropdown.list.item
                                        wire:key="version-{{ $option->value }}"
                                        wire:click="selectVersion('{{ $option->value }}')"
                                    >
                                        <span class="flex items-center gap-2">
                                            <span @class(['font-mono text-xs font-bold', 'text-primary-600 dark:text-primary-400' => $option->isSelected, 'text-gray-600 dark:text-gray-300' => ! $option->isSelected])>{{ $option->value }}</span>
                                            <x-filament::badge :color="$option->state->getColor()" size="sm">{{ $option->state->getLabel() }}</x-filament::badge>
                                            <span class="ms-auto text-xs text-gray-400 dark:text-gray-500">{{ $option->meta }}</span>
                                        </span>
                                    </x-filament::dropdown.list.item>
                                @endforeach
                            </x-filament::dropdown.list>
                        </x-filament::dropdown>
                    </div>
                @endif
            </div>

            {{-- linha de metadados --}}
            <div class="flex flex-wrap items-center gap-2 border-b border-gray-200 pb-4 font-mono text-xs text-gray-400 dark:border-white/10 dark:text-gray-500">
                <span>{{ $entry->qualified_id }}</span>
                <span>·</span>
                <span>{{ __('panel-portal::portal.reader.updated', ['date' => $updatedAt]) }}</span>
                <span>·</span>
                <span>{{ $authorship->byLine }}</span>
            </div>

            {{-- banner: lendo versão antiga do PRD --}}
            @if ($versionStack->isReadingOldVersion())
                <div class="flex flex-wrap items-center gap-3 rounded-lg bg-warning-50 px-4 py-3 text-sm text-warning-700 ring-1 ring-warning-600/20 dark:bg-warning-500/10 dark:text-warning-400 dark:ring-warning-400/20">
                    <x-filament::icon icon="heroicon-o-clock" class="size-5 flex-none" />
                    <span class="flex-1">{{ __('panel-portal::portal.prd.old_version_banner', ['version' => $versionStack->selectedLabel(), 'date' => $selectedFrozenAt ?? '']) }}</span>
                    <x-filament::button color="warning" size="xs" outlined wire:click="selectVersion(null)">
                        {{ __('panel-portal::portal.prd.view_latest', ['version' => $versionStack->latestLabel()]) }}
                    </x-filament::button>
                </div>
            @endif

            {{-- banner: espelho somente leitura --}}
            @if ($entry->origin === Origin::Mirror)
                <div class="flex items-center gap-3 rounded-lg bg-info-50 px-4 py-3 text-sm text-info-700 ring-1 ring-info-600/20 dark:bg-info-500/10 dark:text-info-400 dark:ring-info-400/20">
                    <x-filament::icon icon="heroicon-o-lock-closed" class="size-5 flex-none" />
                    {{ __('panel-portal::portal.reader.mirror_banner') }}
                </div>
            @endif

            {{-- corpo --}}
            @if ($bodyHtml !== null)
                <div class="prose max-w-none dark:prose-invert">{!! $bodyHtml !!}</div>
            @else
                <div class="rounded-xl border border-dashed border-gray-300 px-6 py-12 text-center dark:border-white/10">
                    <p class="text-sm font-semibold text-gray-500 dark:text-gray-400">{{ __('panel-portal::portal.reader.no_body_title') }}</p>
                    <p class="mt-1 text-sm text-gray-400 dark:text-gray-500">{{ __('panel-portal::portal.reader.no_body_text') }}</p>
                </div>
            @endif

            {{-- ligações tipadas --}}
            @if ($links !== [])
                <x-filament::section>
                    <x-slot name="heading">{{ __('panel-portal::portal.reader.links') }}</x-slot>

                    <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                        @foreach ($links as $link)
                            <a
                                href="{{ $link->url }}"
                                wire:key="link-{{ $loop->index }}"
                                class="flex flex-col gap-1 rounded-lg px-3 py-3 ring-1 ring-gray-950/5 transition hover:bg-gray-50 dark:ring-white/10 dark:hover:bg-white/5"
                            >
                                <span class="font-mono text-xs uppercase tracking-wide text-primary-600 dark:text-primary-400">{{ $link->label }}</span>
                                <span class="text-sm font-semibold text-gray-950 dark:text-white">{{ $link->title }}</span>
                                <span class="font-mono text-xs text-gray-400 dark:text-gray-500">{{ $link->qualifiedId }}</span>
                            </a>
                        @endforeach
                    </div>
                </x-filament::section>
            @endif

            {{-- artefatos --}}
            @if ($entry->artifacts->isNotEmpty())
                <x-filament::section compact>
                    <x-slot name="heading">{{ __('panel-portal::portal.reader.artifacts') }}</x-slot>

                    <div class="flex flex-col gap-2">
                        @foreach ($entry->artifacts as $artifact)
                            <a
                                href="{{ $artifact->url }}"
                                target="_blank"
                                rel="noopener"
                                wire:key="artifact-{{ $artifact->id }}"
                                class="inline-flex items-center gap-2 break-all text-sm text-primary-600 hover:underline dark:text-primary-400"
                            >
                                <x-filament::icon icon="heroicon-m-link" class="size-4 flex-none" />
                                {{ $artifact->url }}
                            </a>
                        @endforeach
                    </div>
                </x-filament::section>
            @endif

            {{-- anterior / próximo --}}
            <div class="grid grid-cols-1 gap-3 border-t border-gray-200 pt-5 sm:grid-cols-2 dark:border-white/10">
                @if ($previous !== null)
                    <a href="{{ $context->entryUrl($previous) }}" class="flex flex-col gap-1 rounded-lg px-4 py-3 text-left ring-1 ring-gray-950/5 transition hover:bg-gray-50 dark:ring-white/10 dark:hover:bg-white/5">
                        <span class="font-mono text-xs uppercase tracking-wide text-gray-400 dark:text-gray-500">{{ __('panel-portal::portal.reader.previous') }}</span>
                        <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">{{ $previous->title }}</span>
                    </a>
                @else
                    <span></span>
                @endif
                @if ($next !== null)
                    <a href="{{ $context->entryUrl($next) }}" class="flex flex-col gap-1 rounded-lg px-4 py-3 text-right ring-1 ring-gray-950/5 transition hover:bg-gray-50 dark:ring-white/10 dark:hover:bg-white/5">
                        <span class="font-mono text-xs uppercase tracking-wide text-gray-400 dark:text-gray-500">{{ __('panel-portal::portal.reader.next') }}</span>
                        <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">{{ $next->title }}</span>
                    </a>
                @endif
            </div>
        </div>

        {{-- rail direito --}}
        <aside class="space-y-4">
            @if (count($toc) > 1)
                <x-filament::section compact>
                    <x-slot name="heading">{{ __('panel-portal::portal.reader.on_this_page') }}</x-slot>

                    <ul class="space-y-1 border-s border-gray-200 dark:border-white/10">
                        @foreach ($toc as $heading)
                            <li>
                                <a href="#{{ $heading['slug'] }}" class="-ms-px block border-s-2 border-transparent py-1 ps-3 text-sm text-gray-500 transition hover:border-primary-600 hover:text-primary-600 dark:text-gray-400 dark:hover:border-primary-400 dark:hover:text-primary-400">{{ $heading['text'] }}</a>
                            </li>
                        @endforeach
                    </ul>
                </x-filament::section>
            @endif

            @if (! $versionStack->isEmpty())
                <x-filament::section compact>
                    <x-slot name="heading">{{ __('panel-portal::portal.prd.versions') }}</x-slot>

                    <div class="flex flex-col gap-1">
                        @foreach ($versionStack->options() as $option)
                            <button
                                type="button"
                                wire:key="rail-version-{{ $option->value }}"
                                wire:click="selectVersion('{{ $option->value }}')"
                                @class([
                                    'flex items-center gap-2 rounded-lg px-2 py-2 text-left transition hover:bg-gray-50 dark:hover:bg-white/5',
                                    'bg-primary-50 dark:bg-primary-500/10' => $option->isSelected,
                                ])
                            >
                                <span @class(['font-mono text-xs font-bold', 'text-primary-600 dark:text-primary-400' => $option->isSelected, 'text-gray-600 dark:text-gray-300' => ! $option->isSelected])>{{ $option->value }}</span>
                                <x-filament::badge :color="$option->state->getColor()" size="sm">{{ $option->state->getLabel() }}</x-filament::badge>
                                <span class="ms-auto text-end font-mono text-xs text-gray-400 dark:text-gray-500">{{ $option->meta }}</span>
                            </button>
                        @endforeach
                    </div>
                </x-filament::section>
            @endif

            <x-filament::section compact>
                <x-slot name="heading">{{ __('panel-portal::portal.reader.about') }}</x-slot>

                <dl class="grid grid-cols-[auto_1fr] items-center gap-x-4 gap-y-2 text-sm">
                    <dt class="text-gray-400 dark:text-gray-500">{{ __('panel-portal::portal.reader.department') }}</dt>
                    <dd class="text-gray-700 dark:text-gray-200">{{ $entry->department->getLabel() }}</dd>

                    <dt class="text-gray-400 dark:text-gray-500">{{ __('panel-portal::portal.reader.audience') }}</dt>
                    <dd class="text-gray-700 dark:text-gray-200">{{ $audienceText }}</dd>

                    <dt class="text-gray-400 dark:text-gray-500">{{ $authorship->label }}</dt>
                    <dd class="text-gray-700 dark:text-gray-200">{{ $authorship->names }}</dd>

                    <dt class="text-gray-400 dark:text-gray-500">{{ __('panel-portal::portal.reader.status') }}</dt>
                    <dd><x-filament::badge :color="$entry->status->getColor()" size="sm">{{ $entry->status->getLabel() }}</x-filament::badge></dd>
                </dl>

                @if (($entry->keywords ?? []) !== [])
                    <div class="mt-3 flex flex-wrap gap-1.5">
                        @foreach ($entry->keywords as $keyword)
                            <x-filament::badge color="gray" size="sm">{{ $keyword }}</x-filament::badge>
                        @endforeach
                    </div>
                @endif
            </x-filament::section>

            @if ($sourceUrl !== null)
                <div>
                    <x-filament::button
                        tag="a"
                        :href="$sourceUrl"
                        target="_blank"
                        rel="noopener"
                        color="primary"
                        outlined
                        icon="heroicon-m-arrow-top-right-on-square"
                        class="w-full"
                    >
                        {{ __('panel-portal::portal.reader.view_source') }}
                    </x-filament::button>
                    <p class="mt-2 break-all text-center font-mono text-xs text-gray-400 dark:text-gray-500">{{ $entry->document?->git_pointer }}</p>
                </div>
            @endif
        </aside>
    </div>
</x-filament-panels::page>
