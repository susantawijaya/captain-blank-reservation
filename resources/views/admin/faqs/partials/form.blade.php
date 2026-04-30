<form class="form-grid mt-6" method="POST" action="{{ $action }}">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="field">
        <label for="question">Pertanyaan</label>
        <input id="question" name="question" type="text" value="{{ old('question', $faq->question) }}" required>
        @error('question')
            <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="field">
        <label for="answer">Jawaban</label>
        <textarea id="answer" name="answer" required>{{ old('answer', $faq->answer) }}</textarea>
        @error('answer')
            <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="field">
        <label for="sort_order">Urutan</label>
        <input id="sort_order" name="sort_order" type="number" min="0" value="{{ old('sort_order', $faq->sort_order) }}" required>
        @error('sort_order')
            <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="flex flex-wrap gap-3">
        <button class="button primary" type="submit">{{ $submitLabel }}</button>
        @if (!empty($showDelete))
            <button class="button secondary !border-red-200 !text-red-700 hover:!bg-red-50" type="submit" form="delete-faq-form">Hapus</button>
        @endif
    </div>
</form>
@if (!empty($showDelete))
    <form id="delete-faq-form" method="POST" action="{{ $deleteAction }}" onsubmit="return confirm('Hapus FAQ ini?');">
        @csrf
        @method('DELETE')
    </form>
@endif
