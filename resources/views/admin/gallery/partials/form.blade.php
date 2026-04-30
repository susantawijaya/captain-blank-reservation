<form class="form-grid mt-6" method="POST" action="{{ $action }}" enctype="multipart/form-data">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="field">
        <label for="title">Judul</label>
        <input id="title" name="title" type="text" value="{{ old('title', $galleryItem->title) }}" required>
        @error('title')
            <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="form-grid two">
        <div class="field">
            <label for="category">Kategori</label>
            <input id="category" name="category" type="text" value="{{ old('category', $galleryItem->category) }}" required>
            @error('category')
                <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <div class="field">
            <label class="mb-2 block text-sm font-black text-slate-700">Tampilkan di beranda</label>
            <label class="inline-flex min-h-11 items-center gap-2 rounded-lg border border-slate-300 px-3 py-3 text-sm text-slate-700">
                <input name="is_featured" type="checkbox" value="1" @checked(old('is_featured', $galleryItem->is_featured))>
                Jadikan foto unggulan
            </label>
        </div>
    </div>

    <div class="field">
        <label for="image">Foto</label>
        <input id="image" name="image" type="file" accept="image/*">
        @if ($galleryItem->image_path)
            <p class="mt-2 text-sm text-slate-500">File saat ini: {{ $galleryItem->image_path }}</p>
        @endif
        @error('image')
            <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="field">
        <label for="caption">Caption</label>
        <textarea id="caption" name="caption">{{ old('caption', $galleryItem->caption) }}</textarea>
        @error('caption')
            <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="flex flex-wrap gap-3">
        <button class="button primary" type="submit">{{ $submitLabel }}</button>
        @if (!empty($showDelete))
            <button class="button secondary !border-red-200 !text-red-700 hover:!bg-red-50" type="submit" form="delete-gallery-form">Hapus</button>
        @endif
    </div>
</form>
@if (!empty($showDelete))
    <form id="delete-gallery-form" method="POST" action="{{ $deleteAction }}" onsubmit="return confirm('Hapus data galeri {{ $galleryItem->title }}?');">
        @csrf
        @method('DELETE')
    </form>
@endif
