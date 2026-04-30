@php($packageLocked = $packageLocked ?? false)
@php($destinationLocked = $destinationLocked ?? false)
@php($selectedPackage = (string) old('snorkeling_package_id', $selectedPackageId ?? ($packages->first()->id ?? '')))
@php($selectedDestination = (string) old('destination_id', $selectedDestinationId ?? ''))
@php($selectedBookingDate = old('booking_date', $selectedBookingDate ?? ($availabilityFilters['date_input'] ?? now()->addDay()->toDateString())))
@php($selectedSchedule = (string) old('schedule_id', $selectedScheduleId ?? ''))
@php($selectedPackageModel = $packages->firstWhere('id', (int) $selectedPackage))
@php($destinationOptions = $packages->mapWithKeys(function ($package) {
    return [
        $package->id => $package->destinations
            ->sortBy('name')
            ->map(fn ($destination) => [
                'id' => $destination->id,
                'name' => $destination->name,
            ])
            ->values()
            ->all(),
    ];
})->toArray())
@php($selectedPackageDestinations = collect($destinationOptions[(int) $selectedPackage] ?? []))
@if ($selectedPackageDestinations->isNotEmpty() && ! $selectedPackageDestinations->contains(fn ($destination) => (string) $destination['id'] === (string) $selectedDestination))
    @php($selectedDestination = (string) ($selectedPackageDestinations->first()['id'] ?? ''))
@endif
@php($selectedDestinationModel = $selectedPackageDestinations->first(fn ($destination) => (string) $destination['id'] === (string) $selectedDestination))
@php($visibleSchedules = $schedules->filter(fn ($schedule) => $schedule->snorkeling_package_id === (int) $selectedPackage && $schedule->start_at->toDateString() === $selectedBookingDate)->values())
@php($scheduleOptions = $schedules->map(function ($schedule) {
    return [
        'id' => $schedule->id,
        'package_id' => $schedule->snorkeling_package_id,
        'date' => $schedule->start_at->toDateString(),
        'label' => $schedule->start_at->format('H:i').' - '.$schedule->end_at->format('H:i').' (tersedia '.$schedule->availableBoats().' dari '.$schedule->boat_count.' kapal)',
    ];
})->values()->toArray())
@if ($visibleSchedules->isNotEmpty() && ! $visibleSchedules->contains('id', (int) $selectedSchedule))
    @php($selectedSchedule = (string) $visibleSchedules->first()->id)
@endif
<form class="form-grid" method="POST" action="{{ $action ?? route('reservations.store') }}" data-reservation-form>
    @csrf
    @if (($method ?? 'POST') !== 'POST')
        @method($method)
    @endif
    @if(($availabilityFilters['active'] ?? false) && !empty($availabilityFilters['date']))
        <div class="rounded-lg border border-sky-100 bg-sky-50 p-4 text-sm text-sky-900">
            Hasil jadwal di bawah sudah disesuaikan untuk tanggal {{ \Illuminate\Support\Carbon::parse($availabilityFilters['date'])->translatedFormat('d M Y') }}, {{ $availabilityFilters['adult_count'] }} dewasa, dan {{ $availabilityFilters['child_count'] }} anak.
        </div>
    @endif
    <div class="field">
        <label for="booking_date">Tanggal Reservasi</label>
        <input id="booking_date" name="booking_date" type="date" min="{{ now()->toDateString() }}" value="{{ $selectedBookingDate }}" required data-booking-date>
        <p class="mt-2 text-sm text-slate-600">Pilih dulu tanggal trip, lalu sistem akan menampilkan jam keberangkatan yang tersedia di hari tersebut.</p>
        @error('booking_date')
            <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
        @enderror
    </div>
    @if ($packageLocked && $selectedPackageModel)
        <div class="field">
            <label for="selected_package_display">Paket Dipilih</label>
            <input
                id="selected_package_display"
                type="text"
                value="{{ $selectedPackageModel->name }} - Rp {{ number_format($selectedPackageModel->price, 0, ',', '.') }}"
                readonly
            >
            <input name="snorkeling_package_id" type="hidden" value="{{ $selectedPackageModel->id }}" data-package-select>
            <p class="mt-2 text-sm text-slate-600">Paket ini dibawa langsung dari pilihan sebelumnya, jadi Anda tinggal fokus ke tanggal, jam trip, dan tujuan snorkeling.</p>
            @error('snorkeling_package_id')
                <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
            @enderror
        </div>
    @else
        <div class="field">
            <label for="snorkeling_package_id">Pilih Paket</label>
            <select id="snorkeling_package_id" name="snorkeling_package_id" required data-package-select>
                @foreach($packages as $package)
                    <option value="{{ $package->id }}" @selected((string) $selectedPackage === (string) $package->id)>
                        {{ $package->name }} - Rp {{ number_format($package->price, 0, ',', '.') }}
                    </option>
                @endforeach
            </select>
            @error('snorkeling_package_id')
                <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
            @enderror
        </div>
    @endif
    @if ($destinationLocked && $selectedDestinationModel)
        <div class="field">
            <label for="selected_destination_display">Destinasi Dipilih</label>
            <input
                id="selected_destination_display"
                type="text"
                value="{{ $selectedDestinationModel['name'] }}"
                readonly
            >
            <input name="destination_id" type="hidden" value="{{ $selectedDestinationModel['id'] }}" data-destination-select>
            <p class="mt-2 text-sm text-slate-600">Destinasi ini mengikuti pilihan sebelumnya, jadi reservasi Anda tetap jelas menuju spot yang dipilih.</p>
            @error('destination_id')
                <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
            @enderror
        </div>
    @else
        <div class="field">
            <label for="destination_id">Pilih Destinasi</label>
            <select id="destination_id" name="destination_id" required data-destination-select @disabled($selectedPackageDestinations->isEmpty())>
                @forelse($selectedPackageDestinations as $destination)
                    <option value="{{ $destination['id'] }}" @selected((string) $selectedDestination === (string) $destination['id'])>
                        {{ $destination['name'] }}
                    </option>
                @empty
                    <option value="">Belum ada destinasi untuk paket ini.</option>
                @endforelse
            </select>
            <p class="mt-2 text-sm text-slate-600" data-empty-destination-message @if($selectedPackageDestinations->isNotEmpty()) hidden @endif>
                Paket ini belum memiliki destinasi yang bisa dipilih.
            </p>
            @error('destination_id')
                <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
            @enderror
        </div>
    @endif
    <div class="field">
        <label for="schedule_id">Pilih Jam Trip</label>
        <select id="schedule_id" name="schedule_id" required data-schedule-select @disabled($visibleSchedules->isEmpty())>
            @forelse($visibleSchedules as $schedule)
                <option value="{{ $schedule->id }}" @selected((string) $selectedSchedule === (string) $schedule->id)>
                    {{ $schedule->start_at->format('H:i') }} - {{ $schedule->end_at->format('H:i') }} (tersedia {{ $schedule->availableBoats() }} dari {{ $schedule->boat_count }} kapal)
                </option>
            @empty
                <option value="">Belum ada jam trip tersedia untuk tanggal ini.</option>
            @endforelse
        </select>
        <p class="mt-2 text-sm text-slate-600" data-empty-schedule-message @if($visibleSchedules->isNotEmpty()) hidden @endif>
            Belum ada jam trip dengan kapal tersedia untuk paket dan tanggal yang dipilih.
        </p>
        @error('schedule_id')
            <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
        @enderror
    </div>
    <div class="form-grid two">
        <div class="field">
            <label for="contact_name">Nama Kontak</label>
            <input id="contact_name" name="contact_name" type="text" value="{{ old('contact_name', $reservation->contact_name ?? $user?->name) }}" placeholder="Nama pemesan" required>
            @error('contact_name')
                <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <div class="field">
            <label for="contact_phone">No WhatsApp</label>
            <input id="contact_phone" name="contact_phone" type="text" value="{{ old('contact_phone', $reservation->contact_phone ?? $user?->phone) }}" placeholder="0812..." required>
            @error('contact_phone')
                <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>
    <div class="form-grid two">
        <div class="field">
            <label for="adult_count">Jumlah Dewasa</label>
            <input id="adult_count" name="adult_count" type="number" min="0" max="50" value="{{ old('adult_count', $reservation->adult_count ?? ($availabilityFilters['adult_count'] ?? 2)) }}" required>
            <p class="mt-2 text-sm text-slate-600">Harga paket berlaku per kapal. Isi jumlah peserta dewasa dalam rombongan Anda.</p>
            @error('adult_count')
                <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <div class="field">
            <label for="child_count">Jumlah Anak</label>
            <input id="child_count" name="child_count" type="number" min="0" max="50" value="{{ old('child_count', $reservation->child_count ?? ($availabilityFilters['child_count'] ?? 0)) }}" required>
            @error('child_count')
                <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>
    <div class="form-grid two">
        <div class="field">
            <label for="pickup_location">Lokasi Jemput</label>
            <input id="pickup_location" name="pickup_location" type="text" value="{{ old('pickup_location', $reservation->pickup_location ?? '') }}" placeholder="Pelabuhan / Hotel">
            @error('pickup_location')
                <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>
    <div class="field">
        <label for="notes">Catatan</label>
        <textarea id="notes" name="notes" placeholder="Catatan tambahan">{{ old('notes', $reservation->notes ?? '') }}</textarea>
        @error('notes')
            <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
        @enderror
    </div>
    <button class="button primary" type="submit" data-reservation-submit @disabled($visibleSchedules->isEmpty())>{{ $submitLabel ?? 'Simpan Reservasi' }}</button>
</form>
@once
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('[data-reservation-form]').forEach((form) => {
                const packageField = form.querySelector('[data-package-select]');
                const destinationField = form.querySelector('[data-destination-select]');
                const bookingDateField = form.querySelector('[data-booking-date]');
                const scheduleSelect = form.querySelector('[data-schedule-select]');
                const emptyDestinationMessage = form.querySelector('[data-empty-destination-message]');
                const emptyMessage = form.querySelector('[data-empty-schedule-message]');
                const submitButton = form.querySelector('[data-reservation-submit]');

                if (!packageField || !scheduleSelect || !bookingDateField) {
                    return;
                }

                const scheduleOptions = @json($scheduleOptions);
                const destinationOptions = @json($destinationOptions);

                const renderDestinations = () => {
                    if (!destinationField || destinationField.tagName !== 'SELECT') {
                        return;
                    }

                    const selectedPackageId = packageField.value;
                    const currentDestinationId = destinationField.value;
                    const matchingDestinations = destinationOptions[String(selectedPackageId)] ?? [];

                    destinationField.innerHTML = '';

                    if (!matchingDestinations.length) {
                        destinationField.disabled = true;
                        const option = document.createElement('option');
                        option.value = '';
                        option.textContent = 'Belum ada destinasi untuk paket ini.';
                        destinationField.appendChild(option);

                        if (emptyDestinationMessage) {
                            emptyDestinationMessage.hidden = false;
                        }

                        if (submitButton) {
                            submitButton.disabled = true;
                        }

                        return;
                    }

                    destinationField.disabled = false;
                    matchingDestinations.forEach((destination, index) => {
                        const option = document.createElement('option');
                        option.value = destination.id;
                        option.textContent = destination.name;
                        option.selected = String(destination.id) === String(currentDestinationId) || (!currentDestinationId && index === 0);
                        destinationField.appendChild(option);
                    });

                    if (!matchingDestinations.some((destination) => String(destination.id) === String(currentDestinationId))) {
                        destinationField.value = String(matchingDestinations[0].id);
                    }

                    if (emptyDestinationMessage) {
                        emptyDestinationMessage.hidden = true;
                    }
                };

                const renderSchedules = () => {
                    const selectedPackageId = packageField.value;
                    const selectedBookingDate = bookingDateField.value;
                    const currentScheduleId = scheduleSelect.value;
                    const matchingSchedules = scheduleOptions.filter((schedule) => (
                        String(schedule.package_id) === String(selectedPackageId)
                        && String(schedule.date) === String(selectedBookingDate)
                    ));

                    scheduleSelect.innerHTML = '';

                    if (!matchingSchedules.length) {
                        scheduleSelect.disabled = true;
                        if (submitButton) {
                            submitButton.disabled = true;
                        }
                        const option = document.createElement('option');
                        option.value = '';
                        option.textContent = 'Belum ada jam trip tersedia untuk tanggal ini.';
                        scheduleSelect.appendChild(option);

                        if (emptyMessage) {
                            emptyMessage.hidden = false;
                        }

                        return;
                    }

                    scheduleSelect.disabled = false;
                    if (submitButton) {
                        submitButton.disabled = false;
                    }
                    matchingSchedules.forEach((schedule, index) => {
                        const option = document.createElement('option');
                        option.value = schedule.id;
                        option.textContent = schedule.label;
                        option.selected = String(schedule.id) === String(currentScheduleId) || (!currentScheduleId && index === 0);
                        scheduleSelect.appendChild(option);
                    });

                    if (!matchingSchedules.some((schedule) => String(schedule.id) === String(currentScheduleId))) {
                        scheduleSelect.value = String(matchingSchedules[0].id);
                    }

                    if (emptyMessage) {
                        emptyMessage.hidden = true;
                    }
                };

                renderDestinations();
                renderSchedules();

                if (packageField.tagName === 'SELECT') {
                    packageField.addEventListener('change', () => {
                        renderDestinations();
                        renderSchedules();
                    });
                }

                bookingDateField.addEventListener('change', renderSchedules);
            });
        });
    </script>
@endonce
