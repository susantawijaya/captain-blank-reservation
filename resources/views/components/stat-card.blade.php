@props(['label', 'value'])

<article class="stat-card">
    <span class="text-sm font-black uppercase tracking-wide text-slate-500">{{ $label }}</span>
    <strong>{{ $value }}</strong>
</article>
