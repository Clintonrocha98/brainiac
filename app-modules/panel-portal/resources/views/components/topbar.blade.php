@php
    $lastSync = \He4rt\Catalog\Models\Project::query()->max('last_synced_at');
    $lastSyncHuman = $lastSync !== null
        ? \Illuminate\Support\Carbon::parse($lastSync)->diffForHumans()
        : null;

    $tabs = [
        ['label' => __('panel-portal::portal.nav.projects'), 'url' => route('portal.projects.index'), 'active' => request()->routeIs('portal.projects.*'), 'index' => request()->routeIs('portal.projects.index')],
        ['label' => __('panel-portal::portal.nav.areas'), 'url' => route('portal.areas.index'), 'active' => request()->routeIs('portal.areas.*'), 'index' => request()->routeIs('portal.areas.index')],
        ['label' => __('panel-portal::portal.nav.collections'), 'url' => route('portal.collections.index'), 'active' => request()->routeIs('portal.collections.*'), 'index' => request()->routeIs('portal.collections.index')],
    ];
@endphp
<header class="sticky top-0 z-40 flex items-center gap-6 px-6 h-14 bg-[rgba(15,14,20,.88)] backdrop-blur-[12px] border-b border-white/7">
    <a href="{{ route('portal.home') }}" class="flex items-center gap-[10px] flex-none">
        <svg width="24" height="24" viewBox="0 0 26 26" fill="none"><rect x="3" y="3" width="20" height="20" rx="6" fill="rgba(139,114,245,.18)"></rect><path d="M8 17.5V8.5h4.4c1.8 0 3 .9 3 2.3 0 1-.6 1.7-1.5 2 1.2.2 2 1.1 2 2.2 0 1.5-1.2 2.5-3.1 2.5H8z" fill="#A48FFA"></path></svg>
        <span class="font-bold text-[15px] tracking-[-.01em]">Brainiac</span>
        <span class="text-[10px] text-faint tracking-[.07em] uppercase font-mono border border-white/10 px-[7px] py-[2px] rounded-full">{{ __('panel-portal::portal.topbar.badge') }}</span>
    </a>

    <nav class="flex gap-0.5 h-full items-stretch flex-none">
        @foreach ($tabs as $tab)
            <a
                href="{{ $tab['url'] }}"
                @class([
                    'flex items-center px-3 text-[13px] border-b-2 hover:text-ink',
                    'font-semibold text-ink' => $tab['active'],
                    'font-normal text-muted' => ! $tab['active'],
                    'border-accent' => $tab['index'],
                    'border-transparent' => ! $tab['index'],
                ])
            >{{ $tab['label'] }}</a>
        @endforeach
    </nav>

    <livewire:panel-portal.global-search />

    @if ($lastSyncHuman !== null)
        <div class="ml-auto flex items-center gap-2 text-[11px] text-faint font-mono flex-none">
            <span class="w-1.5 h-1.5 rounded-full bg-ok inline-block"></span>
            {{ __('panel-portal::portal.topbar.federation_synced', ['time' => $lastSyncHuman]) }}
        </div>
    @endif
</header>
