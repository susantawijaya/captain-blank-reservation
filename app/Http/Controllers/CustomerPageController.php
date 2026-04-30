<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use App\Models\CompanyProfile;
use App\Models\Destination;
use App\Models\Payment;
use App\Models\Reservation;
use App\Models\Review;
use App\Models\Schedule;
use App\Models\SnorkelingPackage;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class CustomerPageController extends Controller
{
    private function currentCustomer(): ?User
    {
        $user = auth()->user();

        return $user instanceof User ? $user : null;
    }

    public function dashboard()
    {
        $user = $this->currentCustomer();

        return view('customer.dashboard', [
            'user' => $user,
            'reservations' => Reservation::with(['package', 'payment'])->where('user_id', $user?->id)->latest()->take(5)->get(),
            'reviews' => Review::with('package')->where('user_id', $user?->id)->latest()->take(3)->get(),
            'messages' => Complaint::query()->where('user_id', $user?->id)->latest()->take(3)->get(),
        ]);
    }

    public function reservations()
    {
        $user = $this->currentCustomer();

        return view('customer.reservations.index', [
            'reservations' => Reservation::with(['package', 'destination', 'schedule', 'payment'])->where('user_id', $user?->id)->latest()->get(),
        ]);
    }

    public function reservationShow(Reservation $reservation)
    {
        abort_unless($reservation->user_id === auth()->id(), 403);

        $reservation->load(['package.destinations', 'destination', 'schedule', 'payment', 'review']);

        return view('customer.reservations.show', [
            'reservation' => $reservation,
            'company' => CompanyProfile::first(),
            'canUploadPayment' => $reservation->canCustomerUploadPayment(),
            'canManageReservation' => $reservation->isCustomerEditable(),
        ]);
    }

    public function reservationEdit(Reservation $reservation)
    {
        abort_unless($reservation->user_id === auth()->id(), 403);
        $reservation->load(['payment', 'package.destinations', 'destination', 'schedule']);
        $this->ensureReservationEditable($reservation);

        return view('customer.reservations.edit', [
            'reservation' => $reservation,
            'user' => $this->currentCustomer(),
            'packages' => SnorkelingPackage::with('destinations')->where('status', 'aktif')->get(),
            'schedules' => Schedule::with('package')
                ->whereHas('package', fn ($query) => $query->where('status', 'aktif'))
                ->where(function ($query) use ($reservation) {
                    $query->where('start_at', '>=', now())
                        ->orWhere('id', $reservation->schedule_id);
                })
                ->where(function ($query) use ($reservation) {
                    $query->where(function ($innerQuery) {
                        $innerQuery
                            ->where('status', 'tersedia')
                            ->whereColumn('booked_count', '<', 'boat_count');
                    })
                        ->orWhere('id', $reservation->schedule_id);
                })
                ->orderBy('start_at')
                ->get(),
            'selectedPackageId' => $reservation->snorkeling_package_id,
            'selectedDestinationId' => $reservation->destination_id,
            'selectedScheduleId' => $reservation->schedule_id,
            'packageLocked' => false,
            'destinationLocked' => false,
            'selectedBookingDate' => old('booking_date', optional($reservation->booking_date)->toDateString()),
            'availabilityFilters' => [
                'active' => false,
                'date' => null,
                'date_input' => $reservation->booking_date?->toDateString(),
                'adult_count' => $reservation->adult_count ?? $reservation->participants,
                'child_count' => $reservation->child_count ?? 0,
            ],
        ]);
    }

    public function payment(Reservation $reservation)
    {
        abort_unless($reservation->user_id === auth()->id(), 403);

        return redirect()->to(route('customer.reservations.show', $reservation).'#pembayaran');
    }

    public function reviews()
    {
        $user = $this->currentCustomer();

        return view('customer.reviews.index', [
            'reviews' => Review::with(['package', 'reservation.destination'])->where('user_id', $user?->id)->latest()->get(),
        ]);
    }

    public function reviewCreate()
    {
        $user = $this->currentCustomer();

        return view('customer.reviews.create', [
            'reservations' => Reservation::with('package')
                ->where('user_id', $user?->id)
                ->where('status', 'selesai')
                ->whereDoesntHave('review')
                ->get(),
        ]);
    }

    public function messages()
    {
        $user = $this->currentCustomer();

        return view('customer.messages.index', [
            'messages' => Complaint::query()
                ->where('user_id', $user?->id)
                ->latest()
                ->get(),
        ]);
    }

    public function messageShow(Complaint $complaint)
    {
        abort_unless($complaint->user_id === auth()->id(), 403);

        return view('customer.messages.show', [
            'message' => $complaint->load('reservation'),
        ]);
    }

    public function profile()
    {
        return view('customer.profile.index', ['user' => $this->currentCustomer()]);
    }

    public function profileEdit()
    {
        return view('customer.profile.edit', ['user' => $this->currentCustomer()]);
    }

    public function reservationUpdate(Request $request, Reservation $reservation): RedirectResponse
    {
        abort_unless($reservation->user_id === auth()->id(), 403);
        $reservation->load(['payment', 'schedule', 'package']);
        $this->ensureReservationEditable($reservation);

        $validated = $this->validateReservation($request);
        $participants = $validated['participants'];
        $package = SnorkelingPackage::query()->findOrFail($validated['snorkeling_package_id']);
        $destination = Destination::query()->findOrFail($validated['destination_id']);
        $schedule = Schedule::query()->with('package')->findOrFail($validated['schedule_id']);
        $bookingDate = $validated['booking_date'];

        $scheduleError = DB::transaction(function () use ($reservation, $validated, $package, $destination, $schedule, $participants, $bookingDate) {
            $currentSchedule = $reservation->schedule_id
                ? Schedule::query()->lockForUpdate()->find($reservation->schedule_id)
                : null;
            $targetSchedule = Schedule::query()->lockForUpdate()->findOrFail($schedule->id);

            $validationError = $this->validateReservationTargets($package, $destination, $targetSchedule, $reservation, $participants, $bookingDate);

            if ($validationError) {
                return $validationError;
            }

            if (! $currentSchedule || $currentSchedule->id !== $targetSchedule->id) {
                if ($currentSchedule) {
                    $releasedCount = max(0, $currentSchedule->booked_count - 1);

                    $currentSchedule->update([
                        'booked_count' => $releasedCount,
                        'status' => $this->scheduleAvailabilityStatus($currentSchedule, $releasedCount),
                    ]);
                }

                $nextBookedCount = $targetSchedule->booked_count + 1;

                $targetSchedule->update([
                    'booked_count' => $nextBookedCount,
                    'status' => $this->scheduleAvailabilityStatus($targetSchedule, $nextBookedCount),
                ]);
            }

            $totalPrice = $package->price;

            $reservation->update([
                'snorkeling_package_id' => $package->id,
                'destination_id' => $destination->id,
                'schedule_id' => $schedule->id,
                'booking_date' => $bookingDate,
                'participants' => $participants,
                'adult_count' => $validated['adult_count'],
                'child_count' => $validated['child_count'],
                'contact_name' => $validated['contact_name'],
                'contact_phone' => $validated['contact_phone'],
                'pickup_location' => $validated['pickup_location'] ?? null,
                'total_price' => $totalPrice,
                'notes' => $validated['notes'] ?? null,
            ]);

            $reservation->payment?->update([
                'amount' => $totalPrice,
            ]);

            return null;
        });

        if ($scheduleError) {
            return back()
                ->withErrors(['schedule_id' => $scheduleError])
                ->withInput();
        }

        return redirect()->route('customer.reservations.show', $reservation)
            ->with('status', 'Reservasi berhasil diperbarui.');
    }

    public function reservationDestroy(Reservation $reservation): RedirectResponse
    {
        abort_unless($reservation->user_id === auth()->id(), 403);
        $reservation->load(['payment', 'schedule']);
        $this->ensureReservationEditable($reservation);

        DB::transaction(function () use ($reservation) {
            $schedule = $reservation->schedule_id
                ? Schedule::query()->lockForUpdate()->find($reservation->schedule_id)
                : null;

            if ($schedule) {
                $releasedCount = max(0, $schedule->booked_count - 1);

                $schedule->update([
                    'booked_count' => $releasedCount,
                    'status' => $this->scheduleAvailabilityStatus($schedule, $releasedCount),
                ]);
            }

            $reservation->delete();
        });

        return redirect()->route('customer.reservations.index')
            ->with('status', 'Reservasi berhasil dihapus.');
    }

    public function paymentStore(Request $request, Reservation $reservation): RedirectResponse
    {
        abort_unless($reservation->user_id === auth()->id(), 403);

        if (! $reservation->loadMissing('payment')->canCustomerUploadPayment()) {
            return redirect()->route('customer.reservations.show', $reservation)
                ->with('error', 'Bukti pembayaran tidak bisa dikirim pada status saat ini.');
        }

        $validated = $request->validate([
            'proof_image' => ['required', 'image', 'max:2048'],
            'notes' => ['nullable', 'string'],
        ]);

        $payment = $reservation->payment ?? new Payment([
            'reservation_id' => $reservation->id,
            'amount' => $reservation->total_price,
            'method' => 'transfer_bank',
            'status' => 'belum_bayar',
        ]);

        if ($payment->proof_image && str_starts_with($payment->proof_image, 'storage/payments/')) {
            Storage::disk('public')->delete(str_replace('storage/', '', $payment->proof_image));
        }

        $payment->fill([
            'amount' => $reservation->total_price,
            'method' => 'transfer_bank',
            'proof_image' => 'storage/'.$request->file('proof_image')->store('payments', 'public'),
            'status' => 'menunggu_verifikasi',
            'notes' => $validated['notes'] ?? null,
            'verified_at' => null,
        ])->save();

        $reservation->update([
            'status' => 'menunggu_verifikasi',
        ]);

        return redirect()->route('customer.reservations.show', $reservation)
            ->with('status', 'Bukti pembayaran berhasil diunggah.');
    }

    public function reviewStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'reservation_id' => ['required', 'integer', 'exists:reservations,id'],
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['required', 'string'],
        ]);

        $reservation = Reservation::with(['package', 'review'])
            ->where('user_id', auth()->id())
            ->where('status', 'selesai')
            ->findOrFail($validated['reservation_id']);

        if ($reservation->review) {
            return back()
                ->withErrors(['reservation_id' => 'Reservasi ini sudah memiliki review.'])
                ->withInput();
        }

        Review::create([
            'reservation_id' => $reservation->id,
            'user_id' => auth()->id(),
            'snorkeling_package_id' => $reservation->snorkeling_package_id,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'],
            'status' => 'draft',
        ]);

        return redirect()->route('customer.reviews.index')
            ->with('status', 'Review berhasil dikirim dan menunggu moderasi admin.');
    }

    public function profileUpdate(Request $request): RedirectResponse
    {
        $user = $this->currentCustomer();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique(User::class, 'email')->ignore($user)],
            'phone' => ['required', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
        ]);

        $user?->update($validated);

        return redirect()->route('customer.profile.index')
            ->with('status', 'Profil berhasil diperbarui.');
    }

    private function validateReservation(Request $request): array
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

    private function ensureReservationEditable(Reservation $reservation): void
    {
        abort_unless($reservation->isCustomerEditable(), 403, 'Reservasi tidak bisa diubah setelah bukti pembayaran dikirim.');
    }

    private function validateReservationTargets(
        SnorkelingPackage $package,
        Destination $destination,
        Schedule $schedule,
        Reservation $reservation,
        int $participants,
        string $bookingDate
    ): ?string {
        if ($package->status !== 'aktif') {
            return 'Paket yang dipilih sedang tidak tersedia.';
        }

        if ($schedule->snorkeling_package_id !== $package->id) {
            return 'Jadwal yang dipilih tidak sesuai dengan paket.';
        }

        if (! $package->destinations()->whereKey($destination->id)->exists()) {
            return 'Destinasi yang dipilih tidak tersedia untuk paket ini.';
        }

        if (! $schedule->package || $schedule->package->status !== 'aktif') {
            return 'Jadwal yang dipilih sedang tidak tersedia.';
        }

        if ($schedule->start_at->toDateString() !== $bookingDate) {
            return 'Tanggal reservasi tidak sesuai dengan jadwal yang dipilih.';
        }

        if ($schedule->id !== $reservation->schedule_id && ! in_array($schedule->status, ['tersedia', 'penuh'], true)) {
            return 'Jadwal yang dipilih sudah tidak tersedia.';
        }

        if ($schedule->id !== $reservation->schedule_id && $schedule->start_at->isPast()) {
            return 'Jadwal yang dipilih sudah lewat.';
        }

        if ($participants > $schedule->capacity) {
            return 'Jumlah peserta melebihi kapasitas maksimal kapal pada jadwal ini.';
        }

        if ($schedule->id !== $reservation->schedule_id && ! $schedule->hasRemainingSlots()) {
            return 'Semua kapal pada jadwal ini sudah terpakai.';
        }

        return null;
    }

    private function scheduleAvailabilityStatus(?Schedule $schedule, int $bookedCount): ?string
    {
        if (! $schedule) {
            return null;
        }

        if (! in_array($schedule->status, ['tersedia', 'penuh'], true)) {
            return $schedule->status;
        }

        return $bookedCount >= $schedule->boat_count ? 'penuh' : 'tersedia';
    }
}
