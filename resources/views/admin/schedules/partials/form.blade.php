@php($tripDatesRaw = old('trip_dates', optional($schedule->start_at)->toDateString() ?: now()->addDay()->toDateString()))
@php($tripDateItems = collect(preg_split('/[\r\n,;]+/', $tripDatesRaw) ?: [])->map(fn ($date) => trim($date))->filter()->values())
@php($tripDateItems = $tripDateItems->isNotEmpty() ? $tripDateItems : collect([now()->addDay()->toDateString()]))
<form class="form-grid mt-6" method="POST" action="{{ $action }}">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="field">
        <label for="snorkeling_package_id">Paket</label>
        <select id="snorkeling_package_id" name="snorkeling_package_id" required>
            @foreach ($packages as $packageOption)
                <option value="{{ $packageOption->id }}" @selected((string) old('snorkeling_package_id', $schedule->snorkeling_package_id) === (string) $packageOption->id)>
                    {{ $packageOption->name }}
                </option>
            @endforeach
        </select>
        @error('snorkeling_package_id')
            <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="field">
        <label for="trip_dates">Tanggal Trip</label>
        <textarea id="trip_dates" name="trip_dates" hidden required>{{ $tripDateItems->implode(PHP_EOL) }}</textarea>
        <div class="mt-3 space-y-3" data-trip-dates>
            @foreach ($tripDateItems as $tripDate)
                <div class="flex flex-wrap items-center gap-3" data-trip-date-row>
                    <input class="flex-1" type="date" value="{{ $tripDate }}" min="{{ now()->toDateString() }}" data-trip-date-input required>
                    <button class="button secondary" type="button" data-remove-trip-date>Hapus</button>
                </div>
            @endforeach
        </div>
        <div class="mt-3 flex flex-wrap gap-3">
            <button class="button secondary" type="button" data-add-trip-date>Tambah Tanggal</button>
        </div>
        <p class="mt-2 text-sm text-slate-600">Tambah satu atau beberapa tanggal. Di sisi pelanggan, semua jadwal ini tetap tampil satu tanggal per satu pilihan.</p>
        @error('trip_dates')
            <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="form-grid three">
        <div class="field">
            <label for="departure_time">Jam Berangkat</label>
            <input id="departure_time" name="departure_time" type="time" value="{{ old('departure_time', optional($schedule->start_at)->format('H:i') ?: '08:00') }}" required>
            @error('departure_time')
                <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <div class="field">
            <label for="return_time">Jam Selesai</label>
            <input id="return_time" name="return_time" type="time" value="{{ old('return_time', optional($schedule->end_at)->format('H:i') ?: '12:00') }}" required>
            @error('return_time')
                <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <div class="field">
            <label for="boat_count">Jumlah Kapal Tersedia</label>
            <input id="boat_count" name="boat_count" type="number" min="1" value="{{ old('boat_count', $schedule->boat_count ?? 3) }}" required>
            <p class="mt-2 text-sm text-slate-600">Kapasitas per kapal mengikuti kapasitas paket yang dipilih.</p>
            @error('boat_count')
                <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="field">
        <label for="status">Status</label>
        <select id="status" name="status" required>
            <option value="tersedia" @selected(old('status', $schedule->status) === 'tersedia')>Tersedia</option>
            <option value="penuh" @selected(old('status', $schedule->status) === 'penuh')>Penuh</option>
            <option value="selesai" @selected(old('status', $schedule->status) === 'selesai')>Selesai</option>
            <option value="batal_cuaca" @selected(old('status', $schedule->status) === 'batal_cuaca')>Batal Cuaca</option>
            <option value="reschedule" @selected(old('status', $schedule->status) === 'reschedule')>Reschedule</option>
        </select>
        @error('status')
            <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="field">
        <label for="weather_note">Catatan Cuaca</label>
        <textarea id="weather_note" name="weather_note">{{ old('weather_note', $schedule->weather_note) }}</textarea>
        @error('weather_note')
            <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="field">
        <label for="destination_note">Catatan Destinasi</label>
        <textarea id="destination_note" name="destination_note">{{ old('destination_note', $schedule->destination_note) }}</textarea>
        @error('destination_note')
            <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="flex flex-wrap gap-3">
        <button class="button primary" type="submit">{{ $submitLabel }}</button>
        @if (!empty($showDelete))
            <button class="button secondary !border-red-200 !text-red-700 hover:!bg-red-50" type="submit" form="delete-schedule-form">Hapus</button>
        @endif
    </div>
</form>
@if (!empty($showDelete))
    <form id="delete-schedule-form" method="POST" action="{{ $deleteAction }}" onsubmit="return confirm('Hapus jadwal ini?');">
        @csrf
        @method('DELETE')
    </form>
@endif
@once
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('form').forEach((form) => {
                const tripDatesField = form.querySelector('#trip_dates');
                const tripDatesContainer = form.querySelector('[data-trip-dates]');
                const addTripDateButton = form.querySelector('[data-add-trip-date]');

                if (!tripDatesField || !tripDatesContainer || !addTripDateButton) {
                    return;
                }

                const syncTripDates = () => {
                    const values = Array.from(tripDatesContainer.querySelectorAll('[data-trip-date-input]'))
                        .map((input) => input.value.trim())
                        .filter(Boolean);

                    tripDatesField.value = values.join('\n');
                };

                const createRow = (value = '') => {
                    const row = document.createElement('div');
                    row.className = 'flex flex-wrap items-center gap-3';
                    row.setAttribute('data-trip-date-row', '');
                    row.innerHTML = `
                        <input class="flex-1" type="date" min="{{ now()->toDateString() }}" value="${value}" data-trip-date-input required>
                        <button class="button secondary" type="button" data-remove-trip-date>Hapus</button>
                    `;

                    const input = row.querySelector('[data-trip-date-input]');
                    const removeButton = row.querySelector('[data-remove-trip-date]');

                    input.addEventListener('input', syncTripDates);
                    removeButton.addEventListener('click', () => {
                        if (tripDatesContainer.querySelectorAll('[data-trip-date-row]').length === 1) {
                            input.value = '';
                            syncTripDates();
                            return;
                        }

                        row.remove();
                        syncTripDates();
                    });

                    return row;
                };

                tripDatesContainer.querySelectorAll('[data-trip-date-row]').forEach((row) => {
                    const input = row.querySelector('[data-trip-date-input]');
                    const removeButton = row.querySelector('[data-remove-trip-date]');

                    input?.addEventListener('input', syncTripDates);
                    removeButton?.addEventListener('click', () => {
                        if (tripDatesContainer.querySelectorAll('[data-trip-date-row]').length === 1) {
                            if (input) {
                                input.value = '';
                            }
                            syncTripDates();
                            return;
                        }

                        row.remove();
                        syncTripDates();
                    });
                });

                addTripDateButton.addEventListener('click', () => {
                    const newRow = createRow();
                    tripDatesContainer.appendChild(newRow);
                    newRow.querySelector('[data-trip-date-input]')?.focus();
                    syncTripDates();
                });

                syncTripDates();
            });
        });
    </script>
@endonce
