<form class="form-grid mt-6" method="POST" action="{{ $action }}">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="field">
        <label for="name">Nama Destinasi</label>
        <input id="name" name="name" type="text" value="{{ old('name', $destination->name) }}" required>
        @error('name')
            <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="form-grid two">
        <div class="field">
            <label for="difficulty">Tingkat Kesulitan</label>
            <select id="difficulty" name="difficulty" required>
                <option value="mudah" @selected(old('difficulty', $destination->difficulty) === 'mudah')>Mudah</option>
                <option value="menengah" @selected(old('difficulty', $destination->difficulty) === 'menengah')>Menengah</option>
                <option value="lanjutan" @selected(old('difficulty', $destination->difficulty) === 'lanjutan')>Lanjutan</option>
            </select>
            @error('difficulty')
                <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <div class="field">
            <label for="status">Status</label>
            <select id="status" name="status" required>
                <option value="aktif" @selected(old('status', $destination->status) === 'aktif')>Aktif</option>
                <option value="nonaktif" @selected(old('status', $destination->status) === 'nonaktif')>Nonaktif</option>
            </select>
            @error('status')
                <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="field">
        <label for="description">Deskripsi</label>
        <textarea id="description" name="description" required>{{ old('description', $destination->description) }}</textarea>
        @error('description')
            <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="flex flex-wrap gap-3">
        <button class="button primary" type="submit">{{ $submitLabel }}</button>
        @if (!empty($showDelete))
            <button class="button secondary !border-red-200 !text-red-700 hover:!bg-red-50" type="submit" form="delete-destination-form">Hapus</button>
        @endif
    </div>
</form>
@if (!empty($showDelete))
    <form id="delete-destination-form" method="POST" action="{{ $deleteAction }}" onsubmit="return confirm('Hapus destinasi {{ $destination->name }}?');">
        @csrf
        @method('DELETE')
    </form>
@endif
