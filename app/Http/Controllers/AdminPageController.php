<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use App\Models\Destination;
use App\Models\Faq;
use App\Models\GalleryItem;
use App\Models\Payment;
use App\Models\Reservation;
use App\Models\Review;
use App\Models\Schedule;
use App\Models\SnorkelingPackage;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AdminPageController extends Controller
{
    public function dashboard()
    {
        return view('admin.dashboard.index', [
            'reservationCount' => Reservation::count(),
            'waitingConfirmationReservations' => Reservation::where('status', 'menunggu_verifikasi')->count(),
            'confirmedReservations' => Reservation::where('status', 'terkonfirmasi')->count(),
            'waitingPaymentReservations' => Reservation::where('status', 'menunggu_pembayaran')->count(),
            'recentReservations' => Reservation::with(['user', 'package', 'payment'])->latest()->take(6)->get(),
        ]);
    }

    public function packages()
    {
        return view('admin.packages.index', ['packages' => SnorkelingPackage::with('destinations')->get()]);
    }

    public function packageCreate()
    {
        return view('admin.packages.create', [
            'package' => new SnorkelingPackage([
                'capacity' => 8,
                'status' => 'aktif',
            ]),
            'destinations' => Destination::all(),
        ]);
    }

    public function packageShow(SnorkelingPackage $package)
    {
        return view('admin.packages.show', ['package' => $package->load(['destinations', 'schedules'])]);
    }

    public function packageEdit(SnorkelingPackage $package)
    {
        return view('admin.packages.edit', ['package' => $package, 'destinations' => Destination::all()]);
    }

    public function packageStore(Request $request): RedirectResponse
    {
        $validated = $this->validatePackage($request);

        $package = SnorkelingPackage::create([
            'name' => $validated['name'],
            'slug' => $this->uniqueSlug(SnorkelingPackage::class, $validated['name']),
            'short_description' => $validated['short_description'],
            'description' => $validated['description'],
            'price' => $validated['price'],
            'duration' => $validated['duration'],
            'capacity' => $validated['capacity'],
            'facilities' => $validated['facilities'] ?? null,
            'image_path' => null,
            'status' => $validated['status'],
        ]);

        $package->destinations()->sync($validated['destination_ids']);

        return redirect()->route('admin.packages.index')
            ->with('status', 'Paket berhasil ditambahkan.');
    }

    public function packageUpdate(Request $request, SnorkelingPackage $package): RedirectResponse
    {
        $validated = $this->validatePackage($request);

        $maxParticipants = $package->reservations()
            ->where('status', '!=', 'dibatalkan')
            ->max('participants') ?? 0;

        if ($maxParticipants > $validated['capacity']) {
            return back()
                ->withErrors(['capacity' => 'Kapasitas paket tidak boleh lebih kecil dari jumlah peserta pada reservasi yang sudah masuk.'])
                ->withInput();
        }

        $package->update([
            'name' => $validated['name'],
            'slug' => $this->uniqueSlug(SnorkelingPackage::class, $validated['name'], $package->id),
            'short_description' => $validated['short_description'],
            'description' => $validated['description'],
            'price' => $validated['price'],
            'duration' => $validated['duration'],
            'capacity' => $validated['capacity'],
            'facilities' => $validated['facilities'] ?? null,
            'status' => $validated['status'],
        ]);

        $package->destinations()->sync($validated['destination_ids']);
        $package->schedules()->update(['capacity' => $validated['capacity']]);

        return redirect()->route('admin.packages.index')
            ->with('status', 'Paket berhasil diperbarui.');
    }

    public function packageDestroy(SnorkelingPackage $package): RedirectResponse
    {
        $package->loadCount(['reservations', 'schedules']);

        if ($package->reservations_count > 0 || $package->schedules_count > 0) {
            return redirect()->route('admin.packages.index')
                ->with('error', 'Paket tidak bisa dihapus karena sudah terhubung dengan jadwal atau reservasi.');
        }

        $package->destinations()->detach();
        $package->delete();

        return redirect()->route('admin.packages.index')
            ->with('status', 'Paket berhasil dihapus.');
    }

    public function destinations()
    {
        return view('admin.destinations.index', ['destinations' => Destination::all()]);
    }

    public function destinationCreate()
    {
        return view('admin.destinations.create', [
            'destination' => new Destination([
                'difficulty' => 'mudah',
                'status' => 'aktif',
            ]),
        ]);
    }

    public function destinationEdit(Destination $destination)
    {
        return view('admin.destinations.edit', ['destination' => $destination]);
    }

    public function destinationStore(Request $request): RedirectResponse
    {
        $validated = $this->validateDestination($request);

        Destination::create([
            'name' => $validated['name'],
            'slug' => $this->uniqueSlug(Destination::class, $validated['name']),
            'description' => $validated['description'],
            'image_path' => null,
            'difficulty' => $validated['difficulty'],
            'status' => $validated['status'],
        ]);

        return redirect()->route('admin.destinations.index')
            ->with('status', 'Destinasi berhasil ditambahkan.');
    }

    public function destinationUpdate(Request $request, Destination $destination): RedirectResponse
    {
        $validated = $this->validateDestination($request);

        $destination->update([
            'name' => $validated['name'],
            'slug' => $this->uniqueSlug(Destination::class, $validated['name'], $destination->id),
            'description' => $validated['description'],
            'difficulty' => $validated['difficulty'],
            'status' => $validated['status'],
        ]);

        return redirect()->route('admin.destinations.index')
            ->with('status', 'Destinasi berhasil diperbarui.');
    }

    public function destinationDestroy(Destination $destination): RedirectResponse
    {
        $destination->loadCount('packages');

        if ($destination->packages_count > 0) {
            return redirect()->route('admin.destinations.index')
                ->with('error', 'Destinasi tidak bisa dihapus karena masih dipakai oleh paket.');
        }

        $destination->delete();

        return redirect()->route('admin.destinations.index')
            ->with('status', 'Destinasi berhasil dihapus.');
    }

    public function schedules(Request $request)
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
            'date' => ['nullable', 'date_format:Y-m-d'],
            'status' => ['nullable', Rule::in(['all', 'tersedia', 'penuh', 'selesai', 'batal_cuaca', 'reschedule'])],
        ]);

        $keyword = trim($validated['q'] ?? '');
        $date = $validated['date'] ?? '';
        $status = $validated['status'] ?? 'all';

        $schedules = Schedule::query()
            ->select('schedules.*')
            ->join('snorkeling_packages', 'snorkeling_packages.id', '=', 'schedules.snorkeling_package_id')
            ->with('package')
            ->when($keyword !== '', fn ($query) => $query->where('snorkeling_packages.name', 'like', "%{$keyword}%"))
            ->when($date !== '', fn ($query) => $query->whereDate('schedules.start_at', $date))
            ->when($status !== 'all', fn ($query) => $query->where('schedules.status', $status))
            ->orderByRaw('DATE(schedules.start_at) asc')
            ->orderByRaw('TIME(schedules.start_at) asc')
            ->orderBy('snorkeling_packages.name')
            ->get();

        return view('admin.schedules.index', [
            'schedules' => $schedules,
            'filters' => [
                'q' => $keyword,
                'date' => $date,
                'status' => $status,
            ],
        ]);
    }

    public function scheduleCreate()
    {
        return view('admin.schedules.create', [
            'schedule' => new Schedule([
                'boat_count' => 3,
                'status' => 'tersedia',
            ]),
            'packages' => SnorkelingPackage::all(),
        ]);
    }

    public function scheduleEdit(Schedule $schedule)
    {
        return view('admin.schedules.edit', ['schedule' => $schedule->load('package'), 'packages' => SnorkelingPackage::all()]);
    }

    public function scheduleStore(Request $request): RedirectResponse
    {
        $validated = $this->validateScheduleCreate($request);
        $package = SnorkelingPackage::query()->findOrFail($validated['snorkeling_package_id']);
        $dates = $this->parseScheduleDates($validated['trip_dates']);

        foreach ($dates as $date) {
            $startAt = Carbon::parse($date->toDateString().' '.$validated['departure_time']);
            $endAt = Carbon::parse($date->toDateString().' '.$validated['return_time']);

            if ($endAt->lessThanOrEqualTo($startAt)) {
                $endAt->addDay();
            }

            $conflictExists = Schedule::query()
                ->where('snorkeling_package_id', $package->id)
                ->where('start_at', $startAt)
                ->exists();

            if ($conflictExists) {
                return back()
                    ->withErrors([
                        'trip_dates' => 'Sudah ada jadwal untuk paket ini pada '.Carbon::parse($startAt)->translatedFormat('d M Y H:i').'.',
                    ])
                    ->withInput();
            }

            Schedule::create([
                'snorkeling_package_id' => $package->id,
                'start_at' => $startAt,
                'end_at' => $endAt,
                'capacity' => $package->capacity,
                'boat_count' => $validated['boat_count'],
                'booked_count' => 0,
                'status' => $validated['status'],
                'weather_note' => $validated['weather_note'] ?? null,
                'destination_note' => $validated['destination_note'] ?? null,
            ]);
        }

        return redirect()->route('admin.schedules.index')
            ->with('status', 'Jadwal berhasil ditambahkan untuk '.count($dates).' hari trip.');
    }

    public function scheduleUpdate(Request $request, Schedule $schedule): RedirectResponse
    {
        $validated = $this->validateScheduleUpdate($request);
        $package = SnorkelingPackage::query()->findOrFail($validated['snorkeling_package_id']);
        $dates = $this->parseScheduleDates($validated['trip_dates']);
        $primaryDate = $dates[0];

        $maxParticipants = $schedule->reservations()
            ->where('status', '!=', 'dibatalkan')
            ->max('participants') ?? 0;

        if ($maxParticipants > $package->capacity) {
            return back()
                ->withErrors(['snorkeling_package_id' => 'Kapasitas paket tidak boleh lebih kecil dari jumlah peserta pada reservasi yang sudah masuk.'])
                ->withInput();
        }

        if ($schedule->booked_count > $validated['boat_count']) {
            return back()
                ->withErrors(['boat_count' => 'Jumlah kapal tidak boleh lebih kecil dari reservasi yang sudah masuk pada jadwal ini.'])
                ->withInput();
        }

        $startAt = Carbon::parse($primaryDate->toDateString().' '.$validated['departure_time']);
        $endAt = Carbon::parse($primaryDate->toDateString().' '.$validated['return_time']);

        if ($endAt->lessThanOrEqualTo($startAt)) {
            $endAt->addDay();
        }

        foreach ($dates as $index => $date) {
            $candidateStartAt = Carbon::parse($date->toDateString().' '.$validated['departure_time']);
            $conflictQuery = Schedule::query()
                ->where('snorkeling_package_id', $package->id)
                ->where('start_at', $candidateStartAt);

            if ($index === 0) {
                $conflictQuery->whereKeyNot($schedule->id);
            }

            if ($conflictQuery->exists()) {
                return back()
                    ->withErrors(['trip_dates' => 'Sudah ada jadwal lain untuk paket ini pada '.Carbon::parse($candidateStartAt)->translatedFormat('d M Y H:i').'.'])
                    ->withInput();
            }
        }

        DB::transaction(function () use ($schedule, $package, $validated, $dates, $startAt, $endAt) {
            $schedule->update([
                'snorkeling_package_id' => $package->id,
                'start_at' => $startAt,
                'end_at' => $endAt,
                'capacity' => $package->capacity,
                'boat_count' => $validated['boat_count'],
                'status' => $this->scheduleStatusFromCounts($validated['status'], $validated['boat_count'], $schedule->booked_count),
                'weather_note' => $validated['weather_note'] ?? null,
                'destination_note' => $validated['destination_note'] ?? null,
            ]);

            $schedule->reservations()->update([
                'booking_date' => $startAt->toDateString(),
            ]);

            foreach (array_slice($dates, 1) as $extraDate) {
                $extraStartAt = Carbon::parse($extraDate->toDateString().' '.$validated['departure_time']);
                $extraEndAt = Carbon::parse($extraDate->toDateString().' '.$validated['return_time']);

                if ($extraEndAt->lessThanOrEqualTo($extraStartAt)) {
                    $extraEndAt->addDay();
                }

                Schedule::create([
                    'snorkeling_package_id' => $package->id,
                    'start_at' => $extraStartAt,
                    'end_at' => $extraEndAt,
                    'capacity' => $package->capacity,
                    'boat_count' => $validated['boat_count'],
                    'booked_count' => 0,
                    'status' => $validated['status'],
                    'weather_note' => $validated['weather_note'] ?? null,
                    'destination_note' => $validated['destination_note'] ?? null,
                ]);
            }
        });

        return redirect()->route('admin.schedules.index')
            ->with('status', count($dates) > 1
                ? 'Jadwal utama berhasil diperbarui dan tambahan '.(count($dates) - 1).' tanggal baru berhasil dibuat.'
                : 'Jadwal berhasil diperbarui.');
    }

    public function scheduleDestroy(Schedule $schedule): RedirectResponse
    {
        $schedule->loadCount('reservations');

        if ($schedule->reservations_count > 0) {
            return redirect()->route('admin.schedules.index')
                ->with('error', 'Jadwal tidak bisa dihapus karena sudah terhubung dengan reservasi.');
        }

        $schedule->delete();

        return redirect()->route('admin.schedules.index')
            ->with('status', 'Jadwal berhasil dihapus.');
    }

    public function reservations(Request $request)
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string'],
        ]);

        $status = $validated['status'] ?? 'all';
        $reservations = Reservation::with(['user', 'package', 'destination', 'payment'])
            ->when($validated['q'] ?? null, fn ($query, $keyword) => $query->where('code', 'like', "%{$keyword}%"))
            ->when($status !== 'all', fn ($query) => $query->where('status', $status))
            ->latest()
            ->get();

        return view('admin.reservations.index', [
            'reservations' => $reservations,
            'filters' => [
                'q' => $validated['q'] ?? '',
                'status' => $status,
            ],
        ]);
    }

    public function reservationShow(Reservation $reservation)
    {
        return view('admin.reservations.show', [
            'reservation' => $reservation->load(['user', 'package.destinations', 'destination', 'schedule', 'payment', 'review']),
        ]);
    }

    public function reservationUpdate(Request $request, Reservation $reservation): RedirectResponse
    {
        $validated = $request->validate([
            'notes' => ['nullable', 'string'],
        ]);

        if ($reservation->status !== 'terkonfirmasi') {
            return back()
                ->withErrors(['notes' => 'Reservasi hanya bisa ditandai selesai setelah pembayaran dikonfirmasi admin.'])
                ->withInput();
        }

        $reservation->update([
            'status' => 'selesai',
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('admin.reservations.show', $reservation)
            ->with('status', 'Reservasi berhasil ditandai selesai.');
    }

    public function payments()
    {
        return view('admin.payments.index', ['payments' => Payment::with('reservation.user')->latest()->get()]);
    }

    public function paymentShow(Payment $payment)
    {
        return view('admin.payments.show', ['payment' => $payment->load('reservation.user', 'reservation.package')]);
    }

    public function paymentUpdate(Request $request, Payment $payment): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['menunggu_verifikasi', 'diterima', 'ditolak'])],
            'notes' => ['nullable', 'string'],
        ]);

        $verifiedAt = in_array($validated['status'], ['diterima', 'ditolak'], true) ? now() : null;

        $payment->update([
            'status' => $validated['status'],
            'notes' => $validated['notes'] ?? null,
            'verified_at' => $verifiedAt,
        ]);

        $reservationStatus = match ($validated['status']) {
            'diterima' => 'terkonfirmasi',
            'ditolak' => 'menunggu_pembayaran',
            default => 'menunggu_verifikasi',
        };

        $payment->reservation?->update([
            'status' => $reservationStatus,
        ]);

        return redirect()->route('admin.payments.show', $payment)
            ->with('status', 'Verifikasi pembayaran berhasil diperbarui.');
    }

    public function reviews()
    {
        return view('admin.reviews.index', ['reviews' => Review::with(['user', 'package'])->latest()->get()]);
    }

    public function reviewShow(Review $review)
    {
        return view('admin.reviews.show', ['review' => $review->load(['user', 'package', 'reservation'])]);
    }

    public function reviewUpdate(Request $request, Review $review): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['draft', 'published', 'hidden'])],
        ]);

        $review->update($validated);

        return redirect()->route('admin.reviews.show', $review)
            ->with('status', 'Status review berhasil diperbarui.');
    }

    public function reviewDestroy(Review $review): RedirectResponse
    {
        $review->delete();

        return redirect()->route('admin.reviews.index')
            ->with('status', 'Review berhasil dihapus.');
    }

    public function complaints()
    {
        return view('admin.complaints.index', ['complaints' => Complaint::with('user')->latest()->get()]);
    }

    public function complaintShow(Complaint $complaint)
    {
        return view('admin.complaints.show', ['complaint' => $complaint->load(['user', 'reservation'])]);
    }

    public function complaintUpdate(Request $request, Complaint $complaint): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['baru', 'diproses', 'selesai'])],
            'admin_reply' => ['nullable', 'string'],
        ]);

        $adminReply = filled($validated['admin_reply'] ?? null) ? trim($validated['admin_reply']) : null;

        $complaint->update([
            'status' => $validated['status'],
            'admin_reply' => $adminReply,
            'replied_at' => $adminReply
                ? ($complaint->replied_at ?? now())
                : null,
        ]);

        return redirect()->route('admin.complaints.show', $complaint)
            ->with('status', 'Status pesan berhasil diperbarui.');
    }

    public function complaintDestroy(Complaint $complaint): RedirectResponse
    {
        $complaint->delete();

        return redirect()->route('admin.complaints.index')
            ->with('status', 'Pesan berhasil dihapus.');
    }

    public function faqs()
    {
        return view('admin.faqs.index', [
            'faqs' => Faq::query()->orderBy('sort_order')->orderBy('question')->get(),
        ]);
    }

    public function faqCreate()
    {
        return view('admin.faqs.create', [
            'faq' => new Faq([
                'sort_order' => (Faq::max('sort_order') ?? 0) + 1,
            ]),
        ]);
    }

    public function faqStore(Request $request): RedirectResponse
    {
        $validated = $this->validateFaq($request);

        Faq::create($validated);

        return redirect()->route('admin.faqs.index')
            ->with('status', 'FAQ berhasil ditambahkan.');
    }

    public function faqEdit(Faq $faq)
    {
        return view('admin.faqs.edit', ['faq' => $faq]);
    }

    public function faqUpdate(Request $request, Faq $faq): RedirectResponse
    {
        $validated = $this->validateFaq($request);

        $faq->update($validated);

        return redirect()->route('admin.faqs.index')
            ->with('status', 'FAQ berhasil diperbarui.');
    }

    public function faqDestroy(Faq $faq): RedirectResponse
    {
        $faq->delete();

        return redirect()->route('admin.faqs.index')
            ->with('status', 'FAQ berhasil dihapus.');
    }

    public function gallery()
    {
        return view('admin.gallery.index', ['galleryItems' => GalleryItem::latest()->get()]);
    }

    public function galleryCreate()
    {
        return view('admin.gallery.create', [
            'galleryItem' => new GalleryItem([
                'category' => 'snorkeling',
                'is_featured' => false,
            ]),
        ]);
    }

    public function galleryEdit(GalleryItem $galleryItem)
    {
        return view('admin.gallery.edit', ['galleryItem' => $galleryItem]);
    }

    public function galleryStore(Request $request): RedirectResponse
    {
        $validated = $this->validateGallery($request);

        GalleryItem::create([
            'title' => $validated['title'],
            'image_path' => $this->storeGalleryImage($request) ?? 'images/site/hero-ocean.svg',
            'category' => $validated['category'],
            'caption' => $validated['caption'] ?? null,
            'is_featured' => $request->boolean('is_featured'),
        ]);

        return redirect()->route('admin.gallery.index')
            ->with('status', 'Data galeri berhasil ditambahkan.');
    }

    public function galleryUpdate(Request $request, GalleryItem $galleryItem): RedirectResponse
    {
        $validated = $this->validateGallery($request);

        $imagePath = $galleryItem->image_path;
        $storedImage = $this->storeGalleryImage($request);

        if ($storedImage) {
            $this->deleteManagedGalleryImage($galleryItem->image_path);
            $imagePath = $storedImage;
        }

        $galleryItem->update([
            'title' => $validated['title'],
            'image_path' => $imagePath,
            'category' => $validated['category'],
            'caption' => $validated['caption'] ?? null,
            'is_featured' => $request->boolean('is_featured'),
        ]);

        return redirect()->route('admin.gallery.index')
            ->with('status', 'Data galeri berhasil diperbarui.');
    }

    public function galleryDestroy(GalleryItem $galleryItem): RedirectResponse
    {
        $this->deleteManagedGalleryImage($galleryItem->image_path);
        $galleryItem->delete();

        return redirect()->route('admin.gallery.index')
            ->with('status', 'Data galeri berhasil dihapus.');
    }

    private function validatePackage(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'short_description' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'price' => ['required', 'integer', 'min:0'],
            'duration' => ['required', 'string', 'max:100'],
            'capacity' => ['required', 'integer', 'min:1'],
            'facilities' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['aktif', 'nonaktif'])],
            'destination_ids' => ['required', 'array', 'min:1'],
            'destination_ids.*' => ['integer', 'exists:destinations,id'],
        ], [
            'destination_ids.required' => 'Pilih minimal satu destinasi.',
        ]);
    }

    private function validateDestination(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'difficulty' => ['required', Rule::in(['mudah', 'menengah', 'lanjutan'])],
            'status' => ['required', Rule::in(['aktif', 'nonaktif'])],
        ]);
    }

    private function validateScheduleCreate(Request $request): array
    {
        return $request->validate([
            'snorkeling_package_id' => ['required', 'integer', 'exists:snorkeling_packages,id'],
            'trip_dates' => ['required', 'string'],
            'departure_time' => ['required', 'date_format:H:i'],
            'return_time' => ['required', 'date_format:H:i'],
            'boat_count' => ['required', 'integer', 'min:1', 'max:50'],
            'status' => ['required', Rule::in(['tersedia', 'penuh', 'selesai', 'batal_cuaca', 'reschedule'])],
            'weather_note' => ['nullable', 'string'],
            'destination_note' => ['nullable', 'string'],
        ]);
    }

    private function validateScheduleUpdate(Request $request): array
    {
        return $request->validate([
            'snorkeling_package_id' => ['required', 'integer', 'exists:snorkeling_packages,id'],
            'trip_dates' => ['required', 'string'],
            'departure_time' => ['required', 'date_format:H:i'],
            'return_time' => ['required', 'date_format:H:i'],
            'boat_count' => ['required', 'integer', 'min:1', 'max:50'],
            'status' => ['required', Rule::in(['tersedia', 'penuh', 'selesai', 'batal_cuaca', 'reschedule'])],
            'weather_note' => ['nullable', 'string'],
            'destination_note' => ['nullable', 'string'],
        ]);
    }

    private function validateGallery(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:100'],
            'caption' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'max:2048'],
        ]);
    }

    private function validateFaq(Request $request): array
    {
        return $request->validate([
            'question' => ['required', 'string', 'max:255'],
            'answer' => ['required', 'string'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ]);
    }

    private function uniqueSlug(string $modelClass, string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $counter = 2;

        while ($modelClass::query()
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->where('slug', $slug)
            ->exists()) {
            $slug = "{$base}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    private function storeGalleryImage(Request $request): ?string
    {
        if (! $request->hasFile('image')) {
            return null;
        }

        return 'storage/'.$request->file('image')->store('gallery', 'public');
    }

    private function deleteManagedGalleryImage(?string $imagePath): void
    {
        if (! $imagePath || ! Str::startsWith($imagePath, 'storage/gallery/')) {
            return;
        }

        Storage::disk('public')->delete(Str::after($imagePath, 'storage/'));
    }

    private function reservationHoldsCapacity(string $status): bool
    {
        return $status !== 'dibatalkan';
    }

    private function parseScheduleDates(string $tripDates): array
    {
        $dates = [];
        $rawDates = preg_split('/[\r\n,;]+/', $tripDates) ?: [];

        foreach ($rawDates as $rawDate) {
            $trimmedDate = trim($rawDate);

            if ($trimmedDate === '') {
                continue;
            }

            try {
                $date = Carbon::createFromFormat('Y-m-d', $trimmedDate);
            } catch (\Throwable) {
                $date = false;
            }

            if (! $date || $date->format('Y-m-d') !== $trimmedDate) {
                throw ValidationException::withMessages([
                    'trip_dates' => 'Format tanggal harus YYYY-MM-DD dan dipisahkan dengan enter atau koma.',
                ]);
            }

            if ($date->startOfDay()->isPast()) {
                throw ValidationException::withMessages([
                    'trip_dates' => 'Tanggal trip tidak boleh kurang dari hari ini.',
                ]);
            }

            $dates[$date->format('Y-m-d')] = $date->copy()->startOfDay();
        }

        if (empty($dates)) {
            throw ValidationException::withMessages([
                'trip_dates' => 'Isi minimal satu tanggal trip.',
            ]);
        }

        ksort($dates);

        return array_values($dates);
    }

    private function scheduleStatusFromCounts(string $baseStatus, int $boatCount, int $bookedCount): string
    {
        if (! in_array($baseStatus, ['tersedia', 'penuh'], true)) {
            return $baseStatus;
        }

        return $bookedCount >= $boatCount ? 'penuh' : 'tersedia';
    }
}
