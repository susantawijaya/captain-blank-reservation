@php($contactUser = auth()->user())
<form class="form-grid" method="POST" action="{{ route('contact.store') }}">
    @csrf
    <div class="field">
        <label for="name">Nama</label>
        <input id="name" name="name" type="text" value="{{ old('name', $contactUser?->name) }}" placeholder="Nama pelanggan" required>
        @error('name')
            <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
        @enderror
    </div>
    <div class="field">
        <label for="phone">No WhatsApp</label>
        <input id="phone" name="phone" type="text" value="{{ old('phone', $contactUser?->phone) }}" placeholder="0812..." required>
        @error('phone')
            <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
        @enderror
    </div>
    <div class="field">
        <label for="subject">Subjek</label>
        <input id="subject" name="subject" type="text" value="{{ old('subject') }}" placeholder="Pertanyaan reservasi" required>
        @error('subject')
            <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
        @enderror
    </div>
    <div class="field">
        <label for="message">Pesan</label>
        <textarea id="message" name="message" placeholder="Tulis pesan" required>{{ old('message') }}</textarea>
        @error('message')
            <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
        @enderror
    </div>
    <button class="button primary" type="submit">Kirim Pesan</button>
</form>
