@props(['label'])
<div {{ $attributes->merge(['class' => 'flex items-center gap-[7px] text-[10px] tracking-[.09em] uppercase text-faint font-mono']) }}>{{ $label }}<span class="flex-1 h-px bg-white/6"></span></div>
