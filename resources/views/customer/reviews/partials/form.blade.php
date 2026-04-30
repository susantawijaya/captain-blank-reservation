<form class="form-grid" method="POST" action="{{ route('customer.reviews.store') }}">
    @csrf
    <div class="field">
        <label for="reservation_id">Reservasi Selesai</label>
        <select id="reservation_id" name="reservation_id" required>
            @forelse($reservations as $reservation)
                <option value="{{ $reservation->id }}" @selected((string) old('reservation_id') === (string) $reservation->id)>
                    {{ $reservation->code }} - {{ $reservation->package->name }}
                </option>
            @empty
                <option value="">Belum ada reservasi selesai yang bisa direview</option>
            @endforelse
        </select>
        @error('reservation_id')
            <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
        @enderror
    </div>
    <fieldset class="field field-reset">
        <legend>Rating</legend>
        <div class="rating-picker" role="radiogroup" aria-label="Pilih rating bintang">
            @for ($rating = 5; $rating >= 1; $rating--)
                <input
                    class="rating-picker-input"
                    id="rating-{{ $rating }}"
                    name="rating"
                    type="radio"
                    value="{{ $rating }}"
                    @checked((string) old('rating', 5) === (string) $rating)
                    required
                >
                <label class="rating-picker-star" for="rating-{{ $rating }}" aria-label="{{ $rating }} bintang">★</label>
            @endfor
        </div>
        <p class="rating-picker-caption">Klik bintang untuk memberi penilaian.</p>
        @error('rating')
            <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
        @enderror
    </fieldset>
    <div class="field">
        <label for="comment">Komentar</label>
        <textarea id="comment" name="comment" placeholder="Bagaimana pengalaman snorkeling?" required>{{ old('comment') }}</textarea>
        @error('comment')
            <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
        @enderror
    </div>
    <button class="button primary" type="submit" @disabled($reservations->isEmpty())>Simpan Review</button>
</form>
