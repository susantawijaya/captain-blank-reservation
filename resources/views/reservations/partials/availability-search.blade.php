@php($theme = $theme ?? 'dark')
@php($isDarkTheme = $theme === 'dark')
<div class="rounded-2xl p-6 {{ $isDarkTheme ? 'border border-white/20 bg-white/10 backdrop-blur-md' : 'border border-slate-200 bg-white shadow-sm' }}">
    @if (!empty($heading))
        <span class="text-sm font-bold uppercase tracking-[0.16em] {{ $isDarkTheme ? 'text-cyan-100' : 'text-sky-700' }}">{{ $heading }}</span>
    @endif
    @if (!empty($title))
        <h2 class="mt-3 text-3xl font-black {{ $isDarkTheme ? 'text-white' : 'text-slate-950' }}">{{ $title }}</h2>
    @endif
    @if (!empty($description))
        <p class="mt-3 text-sm leading-7 {{ $isDarkTheme ? 'text-sky-50/90' : 'text-slate-600' }}">{{ $description }}</p>
    @endif
    <form class="mt-5 grid gap-4 md:grid-cols-[1.25fr_0.8fr_0.8fr_auto]" method="GET" action="{{ $action }}">
        @foreach(($hiddenFields ?? []) as $hiddenName => $hiddenValue)
            <input type="hidden" name="{{ $hiddenName }}" value="{{ $hiddenValue }}">
        @endforeach
        <div class="field !mb-0">
            <label for="{{ $formId }}_date" class="{{ $isDarkTheme ? '!text-white' : '!text-slate-900' }}">Tanggal Reservasi</label>
            <input
                id="{{ $formId }}_date"
                name="date"
                type="date"
                min="{{ now()->toDateString() }}"
                value="{{ $dateValue }}"
                required
            >
        </div>
        <div class="field !mb-0">
            <label for="{{ $formId }}_adult_count" class="{{ $isDarkTheme ? '!text-white' : '!text-slate-900' }}">Dewasa</label>
            <input
                id="{{ $formId }}_adult_count"
                name="adult_count"
                type="number"
                min="1"
                max="50"
                value="{{ $adultCount }}"
                required
            >
        </div>
        <div class="field !mb-0">
            <label for="{{ $formId }}_child_count" class="{{ $isDarkTheme ? '!text-white' : '!text-slate-900' }}">Anak</label>
            <input
                id="{{ $formId }}_child_count"
                name="child_count"
                type="number"
                min="0"
                max="50"
                value="{{ $childCount }}"
                required
            >
        </div>
        <div class="flex items-end">
            <button class="button primary w-full md:w-auto" type="submit">{{ $submitLabel ?? 'Check Availability' }}</button>
        </div>
    </form>
</div>
