<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use App\Models\CompanyProfile;
use App\Models\Destination;
use App\Models\Faq;
use App\Models\GalleryItem;
use App\Models\Payment;
use App\Models\Reservation;
use App\Models\Review;
use App\Models\Schedule;
use App\Models\SnorkelingPackage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PublicPageController extends Controller
{
    public function home()
    {
        return view('welcome', [
            'company' => CompanyProfile::first(),
            'packages' => $this->featuredHomePackages(),
            'destinations' => $this->featuredHomeDestinations(),
            'reviews' => Review::with(['user', 'package'])->where('status', 'published')->latest()->take(3)->get(),
            'galleryItems' => GalleryItem::where('is_featured', true)->take(6)->get(),
            'faqs' => Faq::orderBy('sort_order')->take(6)->get(),
        ]);
    }

    public function packages(Request $request)
    {
        $availabilityFilters = $this->availabilityFilters($request);
        $selectedDestination = $this->selectedDestinationFilter($request);
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
            'price_range' => ['nullable', 'in:all,lt500000,gte500000'],
        ]);

        $packages = SnorkelingPackage::with([
            'destinations',
            'schedules' => fn ($query) => $this->applyPublicScheduleFilters($query, $availabilityFilters, true),
        ])
            ->where('status', 'aktif')
            ->when($validated['q'] ?? null, function ($query, $keyword) {
                $query->where(function ($innerQuery) use ($keyword) {
                    $innerQuery
                        ->where('name', 'like', "%{$keyword}%")
                        ->orWhere('short_description', 'like', "%{$keyword}%")
                        ->orWhere('description', 'like', "%{$keyword}%");
                });
            })
            ->when(($validated['price_range'] ?? 'all') === 'lt500000', fn ($query) => $query->where('price', '<', 500000))
            ->when(($validated['price_range'] ?? 'all') === 'gte500000', fn ($query) => $query->where('price', '>=', 500000))
            ->when($selectedDestination, fn ($query) => $query->whereHas('destinations', fn ($destinationQuery) => $destinationQuery->whereKey($selectedDestination->id)))
            ->when($availabilityFilters['active'], fn ($query) => $query->whereHas(
                'schedules',
                fn ($scheduleQuery) => $this->applyPublicScheduleFilters($scheduleQuery, $availabilityFilters)
            ))
            ->get();

        return view('packages.index', [
            'packages' => $packages,
            'filters' => [
                'q' => $validated['q'] ?? '',
                'price_range' => $validated['price_range'] ?? 'all',
                'date' => $availabilityFilters['date'],
                'adult_count' => $availabilityFilters['adult_count_input'],
                'child_count' => $availabilityFilters['child_count_input'],
                'destination' => $selectedDestination?->id,
            ],
            'availabilityFilters' => $availabilityFilters,
            'selectedDestination' => $selectedDestination,
        ]);
    }

    public function packageShow(Request $request, SnorkelingPackage $package)
    {
        abort_unless($package->status === 'aktif', 404);
        $availabilityFilters = $this->availabilityFilters($request);
        $selectedDestination = $this->selectedPackageDestination($request, $package);

        return view('packages.show', [
            'package' => $package->load([
                'destinations' => fn ($query) => $query->where('status', 'aktif'),
                'schedules' => fn ($query) => $this->applyPublicScheduleFilters($query, $availabilityFilters, true, 8),
            ]),
            'availabilityFilters' => $availabilityFilters,
            'selectedDestination' => $selectedDestination,
        ]);
    }

    public function destinations(Request $request)
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
            'difficulty' => ['nullable', 'in:all,mudah,menengah,lanjutan'],
        ]);

        $difficulty = $validated['difficulty'] ?? 'all';
        $destinations = Destination::query()
            ->withCount(['packages' => fn ($packageQuery) => $packageQuery->where('status', 'aktif')])
            ->where('status', 'aktif')
            ->when($validated['q'] ?? null, function ($query, $keyword) {
                $query->where(function ($innerQuery) use ($keyword) {
                    $innerQuery
                        ->where('name', 'like', "%{$keyword}%")
                        ->orWhere('description', 'like', "%{$keyword}%");
                });
            })
            ->when($difficulty !== 'all', fn ($query) => $query->where('difficulty', $difficulty))
            ->get();

        return view('destinations.index', [
            'destinations' => $destinations,
            'filters' => [
                'q' => $validated['q'] ?? '',
                'difficulty' => $difficulty,
            ],
        ]);
    }

    public function destinationShow(Destination $destination)
    {
        abort_unless($destination->status === 'aktif', 404);

        return redirect()->route('packages.index', ['destination' => $destination->id]);
    }

    public function schedules(Request $request)
    {
        return redirect()->route('packages.index');
    }

    public function reviews()
    {
        return view('reviews.index', [
            'reviews' => Review::with(['user', 'package'])->where('status', 'published')->latest()->get(),
        ]);
    }

    public function gallery()
    {
        return view('gallery.index', ['galleryItems' => GalleryItem::latest()->get()]);
    }

    public function contact()
    {
        return view('contact.index', [
            'company' => CompanyProfile::first(),
            'faqs' => Faq::orderBy('sort_order')->get(),
        ]);
    }

    public function contactStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string'],
        ]);

        Complaint::create([
            'user_id' => auth()->id(),
            'reservation_id' => null,
            'guest_name' => $validated['name'],
            'guest_phone' => $validated['phone'],
            'subject' => $validated['subject'],
            'message' => $validated['message'],
            'status' => 'baru',
        ]);

        return redirect()->route('contact.index')
            ->with('status', 'Pesan Anda berhasil dikirim ke admin.');
    }

    public function reservationCreate(Request $request)
    {
        $availabilityFilters = $this->availabilityFilters($request);
        $selectedDestination = $this->selectedDestinationFilter($request);
        $packageInput = $request->integer('package');
        $packages = SnorkelingPackage::with(['destinations' => fn ($query) => $query->where('status', 'aktif')])
            ->where('status', 'aktif')
            ->when($selectedDestination, fn ($query) => $query->whereHas('destinations', fn ($destinationQuery) => $destinationQuery->whereKey($selectedDestination->id)))
            ->when($availabilityFilters['active'], fn ($query) => $query->whereHas(
                'schedules',
                fn ($scheduleQuery) => $this->applyReservationScheduleFilters($scheduleQuery, $availabilityFilters)
            ))
            ->get();

        $redirectFilters = collect([
            'date' => $availabilityFilters['date'],
            'adult_count' => $availabilityFilters['adult_count_input'],
            'child_count' => $availabilityFilters['child_count_input'],
            'destination' => $selectedDestination?->id,
        ])->filter(fn ($value) => $value !== null && $value !== '')->all();

        if ($availabilityFilters['active'] && $packages->isEmpty()) {
            return redirect()
                ->route('packages.index', $redirectFilters)
                ->with('error', 'Tidak ada paket yang tersedia untuk tanggal dan jumlah peserta yang dipilih.');
        }

        if ($packageInput && ! $packages->contains('id', $packageInput)) {
            return redirect()
                ->route('packages.index', $redirectFilters)
                ->with('error', 'Paket yang dipilih sudah tidak tersedia untuk filter yang sedang dipakai.');
        }

        $schedules = Schedule::with('package')
            ->whereHas('package', fn ($query) => $query->where('status', 'aktif'))
            ->tap(fn ($query) => $this->applyReservationScheduleFilters($query, $availabilityFilters))
            ->get();

        $selectedPackageId = (int) $packageInput;
        $selectedScheduleId = (int) $request->integer('schedule');
        $selectedSchedule = $selectedScheduleId
            ? $schedules->firstWhere('id', $selectedScheduleId)
            : null;

        if (! $selectedPackageId && $selectedSchedule) {
            $selectedPackageId = $selectedSchedule->snorkeling_package_id;
        }

        if (! $selectedPackageId) {
            $selectedPackageId = (int) ($packages->first()?->id ?? 0);
        }

        $selectedPackage = $packages->firstWhere('id', $selectedPackageId);
        $selectedDestination = $selectedPackage
            ? $this->selectedPackageDestination($request, $selectedPackage)
            : null;

        $packageSchedules = $schedules
            ->where('snorkeling_package_id', $selectedPackageId)
            ->values();

        if ($packageSchedules->isEmpty()) {
            $selectedScheduleId = 0;
        } elseif (! $packageSchedules->contains('id', $selectedScheduleId)) {
            $selectedScheduleId = (int) $packageSchedules->first()->id;
        }

        $selectedBookingDate = old(
            'booking_date',
            $request->input('booking_date')
                ?? $availabilityFilters['date']
                ?? $selectedSchedule?->start_at?->toDateString()
                ?? optional($packageSchedules->first())->start_at?->toDateString()
                ?? now()->addDay()->toDateString()
        );

        return view('reservations.create', [
            'company' => CompanyProfile::first(),
            'user' => $request->user(),
            'packages' => $packages,
            'schedules' => $schedules,
            'selectedPackageId' => $selectedPackageId ?: null,
            'selectedScheduleId' => $selectedScheduleId ?: null,
            'packageLocked' => $request->filled('package') || $request->filled('schedule'),
            'availabilityFilters' => $availabilityFilters,
            'selectedDestinationId' => $selectedDestination?->id,
            'destinationLocked' => $request->filled('destination') && $selectedDestination !== null,
            'selectedBookingDate' => $selectedBookingDate,
        ]);
    }

    public function reservationStore(Request $request): RedirectResponse
    {
        $validated = $this->validateReservationPayload($request);
        $participants = $validated['participants'];
        $package = SnorkelingPackage::query()->findOrFail($validated['snorkeling_package_id']);
        $destination = Destination::query()->findOrFail($validated['destination_id']);
        $schedule = Schedule::query()->with('package')->findOrFail($validated['schedule_id']);
        $bookingDate = $validated['booking_date'];

        if ($package->status !== 'aktif') {
            return back()
                ->withErrors(['snorkeling_package_id' => 'Paket yang dipilih sedang tidak tersedia.'])
                ->withInput();
        }

        if ($schedule->snorkeling_package_id !== $package->id) {
            return back()
                ->withErrors(['schedule_id' => 'Jadwal yang dipilih tidak sesuai dengan paket.'])
                ->withInput();
        }

        if (! $package->destinations()->whereKey($destination->id)->exists()) {
            return back()
                ->withErrors(['destination_id' => 'Destinasi yang dipilih tidak tersedia untuk paket ini.'])
                ->withInput();
        }

        if (! $schedule->package || $schedule->package->status !== 'aktif') {
            return back()
                ->withErrors(['schedule_id' => 'Jadwal yang dipilih sedang tidak tersedia.'])
                ->withInput();
        }

        if ($schedule->start_at->toDateString() !== $bookingDate) {
            return back()
                ->withErrors(['booking_date' => 'Tanggal reservasi tidak sesuai dengan jadwal yang dipilih.'])
                ->withInput();
        }

        if (! $schedule->hasRemainingSlots()) {
            return back()
                ->withErrors(['schedule_id' => 'Jadwal yang dipilih sudah tidak tersedia.'])
                ->withInput();
        }

        if ($schedule->start_at->isPast()) {
            return back()
                ->withErrors(['schedule_id' => 'Jadwal yang dipilih sudah lewat.'])
                ->withInput();
        }

        if ($participants > $schedule->capacity) {
            return back()
                ->withErrors(['adult_count' => 'Jumlah peserta melebihi kapasitas maksimal kapal pada jadwal ini.'])
                ->withInput();
        }

        $reservation = DB::transaction(function () use ($validated, $participants, $request, $package, $destination, $schedule, $bookingDate) {
            $lockedSchedule = Schedule::query()->lockForUpdate()->findOrFail($schedule->id);

            if (! $lockedSchedule->isOpenForBooking()) {
                throw ValidationException::withMessages([
                    'schedule_id' => 'Jadwal yang dipilih baru saja habis atau ditutup admin.',
                ]);
            }

            $totalPrice = $package->price;

            $reservation = Reservation::create([
                'code' => $this->generateReservationCode(),
                'user_id' => $request->user()->id,
                'snorkeling_package_id' => $package->id,
                'destination_id' => $destination->id,
                'schedule_id' => $lockedSchedule->id,
                'booking_date' => $bookingDate,
                'participants' => $participants,
                'adult_count' => $validated['adult_count'],
                'child_count' => $validated['child_count'],
                'contact_name' => $validated['contact_name'],
                'contact_phone' => $validated['contact_phone'],
                'pickup_location' => $validated['pickup_location'] ?? null,
                'total_price' => $totalPrice,
                'status' => 'menunggu_pembayaran',
                'notes' => $validated['notes'] ?? null,
            ]);

            Payment::create([
                'reservation_id' => $reservation->id,
                'amount' => $totalPrice,
                'method' => 'transfer_bank',
                'status' => 'belum_bayar',
            ]);

            $nextBookedCount = $lockedSchedule->booked_count + 1;

            $lockedSchedule->update([
                'booked_count' => $nextBookedCount,
                'status' => $this->scheduleAvailabilityStatus($lockedSchedule, $nextBookedCount),
            ]);

            return $reservation;
        });

        return redirect()->route('reservations.success')
            ->with('status', 'Reservasi berhasil dibuat.')
            ->with('reservation_code', $reservation->code);
    }

    public function reservationSuccess()
    {
        return view('reservations.success');
    }

    private function availabilityFilters(Request $request): array
    {
        $validated = $request->validate([
            'date' => ['nullable', 'date', 'after_or_equal:today'],
            'adult_count' => ['nullable', 'integer', 'min:0', 'max:50'],
            'child_count' => ['nullable', 'integer', 'min:0', 'max:50'],
        ]);

        $date = $validated['date'] ?? null;
        $adultCount = array_key_exists('adult_count', $validated) && $validated['adult_count'] !== null
            ? (int) $validated['adult_count']
            : null;
        $childCount = array_key_exists('child_count', $validated) && $validated['child_count'] !== null
            ? (int) $validated['child_count']
            : null;
        $active = $date !== null && $adultCount !== null && $childCount !== null && ($adultCount + $childCount) > 0;

        return [
            'active' => $active,
            'date' => $date,
            'date_input' => $date ?? now()->addDay()->toDateString(),
            'adult_count' => $active ? $adultCount : max(1, (int) ($adultCount ?? 2)),
            'child_count' => $active ? $childCount : max(0, (int) ($childCount ?? 0)),
            'adult_count_input' => $adultCount ?? 2,
            'child_count_input' => $childCount ?? 0,
            'participants' => $active ? $adultCount + $childCount : null,
        ];
    }

    private function applyPublicScheduleFilters(
        $query,
        array $availabilityFilters,
        bool $includeFilledSchedulesWhenBrowsing = false,
        ?int $limit = null
    ): void {
        $query
            ->where('start_at', '>=', now())
            ->when(
                $includeFilledSchedulesWhenBrowsing && ! $availabilityFilters['active'],
                fn ($builder) => $builder->whereIn('status', ['tersedia', 'penuh']),
                fn ($builder) => $builder
                    ->where('status', 'tersedia')
                    ->whereColumn('booked_count', '<', 'boat_count')
            )
            ->when($availabilityFilters['date'], fn ($builder, string $date) => $builder->whereDate('start_at', $date))
            ->when(
                $availabilityFilters['participants'],
                fn ($builder, int $participants) => $builder->where('capacity', '>=', $participants)
            )
            ->orderBy('start_at');

        if ($limit) {
            $query->limit($limit);
        }
    }

    private function applyReservationScheduleFilters($query, array $availabilityFilters): void
    {
        $query
            ->where('status', 'tersedia')
            ->whereColumn('booked_count', '<', 'boat_count')
            ->where('start_at', '>=', now())
            ->when($availabilityFilters['date'], fn ($builder, string $date) => $builder->whereDate('start_at', $date))
            ->when(
                $availabilityFilters['participants'],
                fn ($builder, int $participants) => $builder->where('capacity', '>=', $participants)
            )
            ->orderBy('start_at');
    }

    private function validateReservationPayload(Request $request): array
    {
        $validator = validator($request->all(), [
            'snorkeling_package_id' => ['required', 'integer', 'exists:snorkeling_packages,id'],
            'destination_id' => ['required', 'integer', 'exists:destinations,id'],
            'schedule_id' => ['required', 'integer', 'exists:schedules,id'],
            'booking_date' => ['required', 'date', 'after_or_equal:today'],
            'contact_name' => ['required', 'string', 'max:255'],
            'contact_phone' => ['required', 'string', 'max:50'],
            'adult_count' => ['required', 'integer', 'min:0', 'max:50'],
            'child_count' => ['required', 'integer', 'min:0', 'max:50'],
            'pickup_location' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $validator->after(function ($validator) use ($request) {
            $participants = (int) $request->input('adult_count', 0) + (int) $request->input('child_count', 0);

            if ($participants < 1) {
                $validator->errors()->add('adult_count', 'Minimal harus ada 1 peserta untuk reservasi.');
            }
        });

        $validated = $validator->validate();
        $validated['participants'] = (int) $validated['adult_count'] + (int) $validated['child_count'];

        return $validated;
    }

    private function selectedDestinationFilter(Request $request): ?Destination
    {
        $validated = $request->validate([
            'destination' => ['nullable', 'integer', 'exists:destinations,id'],
        ]);

        $destinationId = $validated['destination'] ?? null;

        if (! $destinationId) {
            return null;
        }

        return Destination::query()
            ->where('status', 'aktif')
            ->find($destinationId);
    }

    private function selectedPackageDestination(Request $request, SnorkelingPackage $package): ?Destination
    {
        $selectedDestination = $this->selectedDestinationFilter($request);

        if ($selectedDestination && $package->destinations()->whereKey($selectedDestination->id)->exists()) {
            return $selectedDestination;
        }

        $destinations = $package->relationLoaded('destinations')
            ? $package->destinations
            : $package->destinations()->where('status', 'aktif')->get();

        return $destinations->count() === 1 ? $destinations->first() : null;
    }

    private function generateReservationCode(): string
    {
        do {
            $code = 'CBR-'.now()->format('Ymd').'-'.strtoupper(Str::random(4));
        } while (Reservation::query()->where('code', $code)->exists());

        return $code;
    }

    private function featuredHomePackages()
    {
        $query = SnorkelingPackage::with([
            'destinations',
            'schedules' => fn ($scheduleQuery) => $scheduleQuery
                ->whereIn('status', ['tersedia', 'penuh'])
                ->where('start_at', '>=', now())
                ->orderBy('start_at'),
        ])
            ->withCount('reservations')
            ->where('status', 'aktif');

        return Reservation::query()
            ->whereHas('package', fn ($packageQuery) => $packageQuery->where('status', 'aktif'))
            ->exists()
                ? (clone $query)->orderByDesc('reservations_count')->orderBy('name')->take(4)->get()
                : (clone $query)->inRandomOrder()->take(4)->get();
    }

    private function featuredHomeDestinations()
    {
        $query = Destination::withCount([
            'packages' => fn ($packageQuery) => $packageQuery->where('status', 'aktif'),
            'reservations',
        ])->where('status', 'aktif');

        return Reservation::query()
            ->whereHas('destination', fn ($destinationQuery) => $destinationQuery->where('status', 'aktif'))
            ->exists()
                ? (clone $query)->orderByDesc('reservations_count')->orderBy('name')->take(4)->get()
                : (clone $query)->inRandomOrder()->take(4)->get();
    }

    private function scheduleAvailabilityStatus(Schedule $schedule, int $bookedCount): string
    {
        if (! in_array($schedule->status, ['tersedia', 'penuh'], true)) {
            return $schedule->status;
        }

        return $bookedCount >= $schedule->boat_count ? 'penuh' : 'tersedia';
    }
}
