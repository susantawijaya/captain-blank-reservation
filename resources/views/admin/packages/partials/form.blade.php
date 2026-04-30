<form class="form-grid mt-6" method="POST" action="{{ $action }}">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="field">
        <label for="name">Nama Paket</label>
        <input id="name" name="name" type="text" value="{{ old('name', $package->name) }}" required>
        @error('name')
            <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="form-grid two">
        <div class="field">
            <label for="price">Harga</label>
            <input id="price" name="price" type="number" min="0" value="{{ old('price', $package->price) }}" required>
            @error('price')
                <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <div class="field">
            <label for="duration">Durasi</label>
            <input id="duration" name="duration" type="text" value="{{ old('duration', $package->duration) }}" required>
            @error('duration')
                <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="form-grid two">
        <div class="field">
            <label for="capacity">Kapasitas</label>
            <input id="capacity" name="capacity" type="number" min="1" value="{{ old('capacity', $package->capacity) }}" required>
            @error('capacity')
                <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <div class="field">
            <label for="status">Status</label>
            <select id="status" name="status" required>
                <option value="aktif" @selected(old('status', $package->status) === 'aktif')>Aktif</option>
                <option value="nonaktif" @selected(old('status', $package->status) === 'nonaktif')>Nonaktif</option>
            </select>
            @error('status')
                <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="field">
        <label for="short_description">Deskripsi Singkat</label>
        <input id="short_description" name="short_description" type="text" value="{{ old('short_description', $package->short_description) }}" required>
        @error('short_description')
            <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="field">
        <label for="description">Deskripsi Lengkap</label>
        <textarea id="description" name="description" required>{{ old('description', $package->description) }}</textarea>
        @error('description')
            <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="field">
        <label for="destination_ids">Destinasi</label>
        @php($selectedDestinations = collect(old('destination_ids', $package->destinations->pluck('id')->all())))
        <select id="destination_ids" name="destination_ids[]" multiple size="5" required>
            @foreach ($destinations as $destination)
                <option value="{{ $destination->id }}" @selected($selectedDestinations->contains($destination->id))>
                    {{ $destination->name }}
                </option>
            @endforeach
        </select>
        <p class="mt-2 text-sm text-slate-500">Gunakan Ctrl atau Shift untuk memilih lebih dari satu destinasi.</p>
        @error('destination_ids')
            <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
        @enderror
        @error('destination_ids.*')
            <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="field">
        <label for="facilities">Fasilitas</label>
        <textarea id="facilities" name="facilities">{{ old('facilities', $package->facilities) }}</textarea>
        @error('facilities')
            <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="flex flex-wrap gap-3">
        <button class="button primary" type="submit">{{ $submitLabel }}</button>
        @if (!empty($showDelete))
            <button class="button secondary !border-red-200 !text-red-700 hover:!bg-red-50" type="submit" form="delete-package-form">Hapus</button>
        @endif
    </div>
</form>
@if (!empty($showDelete))
    <form id="delete-package-form" method="POST" action="{{ $deleteAction }}" onsubmit="return confirm('Hapus paket {{ $package->name }}?');">
        @csrf
        @method('DELETE')
    </form>
@endif
