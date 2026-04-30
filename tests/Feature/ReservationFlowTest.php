<?php

namespace Tests\Feature;

use App\Models\Complaint;
use App\Models\Destination;
use App\Models\Payment;
use App\Models\Reservation;
use App\Models\Review;
use App\Models\Schedule;
use App\Models\SnorkelingPackage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ReservationFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_must_login_before_submitting_contact_message(): void
    {
        $response = $this->post(route('contact.store'), [
            'name' => 'Pengunjung',
            'phone' => '08123456789',
            'subject' => 'Tanya jadwal',
            'message' => 'Apakah besok masih tersedia?',
        ]);

        $response->assertRedirect(route('login'));
        $this->assertDatabaseMissing('complaints', [
            'guest_name' => 'Pengunjung',
            'guest_phone' => '08123456789',
            'subject' => 'Tanya jadwal',
        ]);
    }

    public function test_customer_can_submit_contact_message(): void
    {
        $customer = User::factory()->create([
            'role' => 'customer',
            'name' => 'Pelanggan Login',
            'phone' => '08123456789',
        ]);

        $response = $this->actingAs($customer)->post(route('contact.store'), [
            'name' => 'Pelanggan Login',
            'phone' => '08123456789',
            'subject' => 'Tanya jadwal',
            'message' => 'Apakah besok masih tersedia?',
        ]);

        $response->assertRedirect(route('contact.index'));
        $this->assertDatabaseHas('complaints', [
            'user_id' => $customer->id,
            'guest_name' => 'Pelanggan Login',
            'guest_phone' => '08123456789',
            'subject' => 'Tanya jadwal',
            'status' => 'baru',
        ]);
    }

    public function test_customer_can_create_reservation_and_payment_stub(): void
    {
        $customer = User::factory()->create([
            'role' => 'customer',
            'phone' => '081200000001',
        ]);

        [$package, $schedule] = $this->createPackageAndSchedule();

        $response = $this->actingAs($customer)->post(route('reservations.store'), [
            'snorkeling_package_id' => $package->id,
            'destination_id' => $package->destinations()->firstOrFail()->id,
            'schedule_id' => $schedule->id,
            'booking_date' => $schedule->start_at->toDateString(),
            'contact_name' => 'Customer Reservasi',
            'contact_phone' => '081200000001',
            'adult_count' => 2,
            'child_count' => 0,
            'pickup_location' => 'Pelabuhan Senggigi',
            'notes' => 'Mohon info jam kumpul',
        ]);

        $response->assertRedirect(route('reservations.success'));

        $reservation = Reservation::query()->firstOrFail();
        $schedule->refresh();

        $this->assertSame($customer->id, $reservation->user_id);
        $this->assertSame('menunggu_pembayaran', $reservation->status);
        $this->assertSame(2, $reservation->participants);
        $this->assertSame(2, $reservation->adult_count);
        $this->assertSame(0, $reservation->child_count);
        $this->assertSame(250000, $reservation->total_price);
        $this->assertSame(1, $schedule->booked_count);

        $this->assertDatabaseHas('payments', [
            'reservation_id' => $reservation->id,
            'amount' => 250000,
            'status' => 'belum_bayar',
        ]);
    }

    public function test_reservation_form_filters_schedule_options_to_selected_package_and_availability(): void
    {
        $customer = User::factory()->create([
            'role' => 'customer',
        ]);

        [$firstPackage, $firstSchedule] = $this->createPackageAndSchedule();
        $firstSchedule->update([
            'start_at' => now()->addDays(6)->setTime(8, 0),
            'end_at' => now()->addDays(6)->setTime(12, 0),
        ]);

        $secondPackage = SnorkelingPackage::query()->create([
            'name' => 'Paket Island Hopping',
            'slug' => 'paket-island-hopping',
            'short_description' => 'Paket island hopping',
            'description' => 'Deskripsi island hopping',
            'price' => 425000,
            'duration' => '6 jam',
            'capacity' => 8,
            'status' => 'aktif',
        ]);

        $secondSchedule = Schedule::query()->create([
            'snorkeling_package_id' => $secondPackage->id,
            'start_at' => now()->addDays(5)->setTime(9, 0),
            'end_at' => now()->addDays(5)->setTime(15, 0),
            'capacity' => 8,
            'boat_count' => 2,
            'booked_count' => 0,
            'status' => 'tersedia',
        ]);

        $response = $this->actingAs($customer)->get(route('reservations.create', [
            'package' => $secondPackage->id,
            'date' => $secondSchedule->start_at->toDateString(),
            'adult_count' => 2,
            'child_count' => 1,
        ]));

        $response->assertOk();
        $response->assertSee('Paket Dipilih');
        $response->assertSee($secondPackage->name);
        $response->assertSee('Buat Reservasi');
        $response->assertSee('<option value="'.$secondSchedule->id.'" selected>', false);
        $response->assertDontSee('<option value="'.$firstSchedule->id.'"', false);
    }

    public function test_customer_cannot_open_reservation_form_for_package_that_is_not_available_in_current_filter(): void
    {
        $customer = User::factory()->create([
            'role' => 'customer',
        ]);

        [$package, $schedule] = $this->createPackageAndSchedule();
        $schedule->update([
            'start_at' => now()->addDays(5)->setTime(8, 0),
            'end_at' => now()->addDays(5)->setTime(12, 0),
            'capacity' => 4,
            'boat_count' => 1,
            'booked_count' => 1,
            'status' => 'penuh',
        ]);

        $response = $this->actingAs($customer)->get(route('reservations.create', [
            'package' => $package->id,
            'date' => $schedule->start_at->toDateString(),
            'adult_count' => 2,
            'child_count' => 0,
        ]));

        $response->assertRedirect(route('packages.index', [
            'date' => $schedule->start_at->toDateString(),
            'adult_count' => 2,
            'child_count' => 0,
        ]));
        $response->assertSessionHas('error', 'Tidak ada paket yang tersedia untuk tanggal dan jumlah peserta yang dipilih.');
    }

    public function test_packages_page_can_filter_by_check_availability_inputs(): void
    {
        [$matchingPackage, $matchingSchedule] = $this->createPackageAndSchedule();
        $matchingSchedule->update([
            'start_at' => now()->addDays(4)->setTime(9, 0),
            'end_at' => now()->addDays(4)->setTime(13, 0),
            'capacity' => 6,
            'boat_count' => 2,
            'booked_count' => 0,
            'status' => 'tersedia',
        ]);

        $fullPackage = SnorkelingPackage::query()->create([
            'name' => 'Paket Penuh',
            'slug' => 'paket-penuh',
            'short_description' => 'Sudah penuh',
            'description' => 'Deskripsi paket penuh',
            'price' => 300000,
            'duration' => '4 jam',
            'capacity' => 4,
            'status' => 'aktif',
        ]);

        Schedule::query()->create([
            'snorkeling_package_id' => $fullPackage->id,
            'start_at' => now()->addDays(4)->setTime(10, 0),
            'end_at' => now()->addDays(4)->setTime(14, 0),
            'capacity' => 4,
            'boat_count' => 1,
            'booked_count' => 1,
            'status' => 'penuh',
        ]);

        $response = $this->get(route('packages.index', [
            'date' => $matchingSchedule->start_at->toDateString(),
            'adult_count' => 3,
            'child_count' => 0,
        ]));

        $response->assertOk();
        $response->assertSee($matchingPackage->name);
        $response->assertDontSee('Paket Penuh');
    }

    public function test_customer_can_upload_payment_proof(): void
    {
        Storage::fake('public');

        $customer = User::factory()->create([
            'role' => 'customer',
        ]);

        [$package, $schedule] = $this->createPackageAndSchedule();

        $reservation = Reservation::query()->create([
            'code' => 'CBR-TEST-001',
            'user_id' => $customer->id,
            'snorkeling_package_id' => $package->id,
            'schedule_id' => $schedule->id,
            'booking_date' => $schedule->start_at->toDateString(),
            'participants' => 1,
            'contact_name' => 'Customer',
            'contact_phone' => '081200000002',
            'pickup_location' => 'Pelabuhan',
            'total_price' => 250000,
            'status' => 'menunggu_pembayaran',
        ]);

        Payment::query()->create([
            'reservation_id' => $reservation->id,
            'amount' => 250000,
            'method' => 'transfer_bank',
            'status' => 'belum_bayar',
        ]);

        $proofImagePath = tempnam(sys_get_temp_dir(), 'proof');
        file_put_contents(
            $proofImagePath,
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9Wn2aL8AAAAASUVORK5CYII=')
        );

        try {
            $response = $this->actingAs($customer)->post(route('customer.reservations.payment.store', $reservation), [
                'proof_image' => new UploadedFile($proofImagePath, 'bukti.png', 'image/png', null, true),
                'notes' => 'Transfer dari BCA',
            ]);

            $response->assertRedirect(route('customer.reservations.show', $reservation));
            $this->assertDatabaseHas('payments', [
                'reservation_id' => $reservation->id,
                'status' => 'menunggu_verifikasi',
                'notes' => 'Transfer dari BCA',
            ]);
            $this->assertDatabaseHas('reservations', [
                'id' => $reservation->id,
                'status' => 'menunggu_verifikasi',
            ]);
        } finally {
            @unlink($proofImagePath);
        }
    }

    public function test_customer_cannot_reupload_payment_proof_after_first_submission(): void
    {
        Storage::fake('public');

        $customer = User::factory()->create([
            'role' => 'customer',
        ]);

        [$package, $schedule] = $this->createPackageAndSchedule();

        $reservation = Reservation::query()->create([
            'code' => 'CBR-TEST-REUPLOAD',
            'user_id' => $customer->id,
            'snorkeling_package_id' => $package->id,
            'schedule_id' => $schedule->id,
            'booking_date' => $schedule->start_at->toDateString(),
            'participants' => 1,
            'contact_name' => 'Customer',
            'contact_phone' => '081200000013',
            'pickup_location' => 'Pelabuhan',
            'total_price' => 250000,
            'status' => 'menunggu_verifikasi',
        ]);

        Payment::query()->create([
            'reservation_id' => $reservation->id,
            'amount' => 250000,
            'method' => 'transfer_bank',
            'status' => 'menunggu_verifikasi',
            'proof_image' => 'storage/payments/bukti-lama.png',
        ]);

        $proofImagePath = tempnam(sys_get_temp_dir(), 'proof');
        file_put_contents(
            $proofImagePath,
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9Wn2aL8AAAAASUVORK5CYII=')
        );

        try {
            $response = $this->actingAs($customer)->post(route('customer.reservations.payment.store', $reservation), [
                'proof_image' => new UploadedFile($proofImagePath, 'bukti-baru.png', 'image/png', null, true),
                'notes' => 'Coba kirim ulang',
            ]);

            $response->assertRedirect(route('customer.reservations.show', $reservation));
            $response->assertSessionHas('error', 'Bukti pembayaran tidak bisa dikirim pada status saat ini.');
            $this->assertDatabaseHas('payments', [
                'reservation_id' => $reservation->id,
                'proof_image' => 'storage/payments/bukti-lama.png',
                'status' => 'menunggu_verifikasi',
            ]);
        } finally {
            @unlink($proofImagePath);
        }
    }

    public function test_customer_can_reupload_payment_proof_after_previous_payment_is_rejected(): void
    {
        Storage::fake('public');

        $customer = User::factory()->create([
            'role' => 'customer',
        ]);

        [$package, $schedule] = $this->createPackageAndSchedule();

        $reservation = Reservation::query()->create([
            'code' => 'CBR-TEST-REJECTED',
            'user_id' => $customer->id,
            'snorkeling_package_id' => $package->id,
            'schedule_id' => $schedule->id,
            'booking_date' => $schedule->start_at->toDateString(),
            'participants' => 1,
            'contact_name' => 'Customer',
            'contact_phone' => '081200000014',
            'pickup_location' => 'Pelabuhan',
            'total_price' => 250000,
            'status' => 'menunggu_pembayaran',
        ]);

        Payment::query()->create([
            'reservation_id' => $reservation->id,
            'amount' => 250000,
            'method' => 'transfer_bank',
            'status' => 'ditolak',
            'proof_image' => 'storage/payments/bukti-ditolak.png',
        ]);

        $proofImagePath = tempnam(sys_get_temp_dir(), 'proof');
        file_put_contents(
            $proofImagePath,
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9Wn2aL8AAAAASUVORK5CYII=')
        );

        try {
            $response = $this->actingAs($customer)->post(route('customer.reservations.payment.store', $reservation), [
                'proof_image' => new UploadedFile($proofImagePath, 'bukti-baru.png', 'image/png', null, true),
                'notes' => 'Upload ulang setelah ditolak',
            ]);

            $response->assertRedirect(route('customer.reservations.show', $reservation));
            $this->assertDatabaseHas('payments', [
                'reservation_id' => $reservation->id,
                'status' => 'menunggu_verifikasi',
                'notes' => 'Upload ulang setelah ditolak',
            ]);
            $this->assertDatabaseHas('reservations', [
                'id' => $reservation->id,
                'status' => 'menunggu_verifikasi',
            ]);
        } finally {
            @unlink($proofImagePath);
        }
    }

    public function test_customer_can_update_reservation_before_sending_payment_proof(): void
    {
        $customer = User::factory()->create([
            'role' => 'customer',
        ]);

        [$package, $schedule] = $this->createPackageAndSchedule();

        $otherPackage = SnorkelingPackage::query()->create([
            'name' => 'Paket Update',
            'slug' => 'paket-update',
            'short_description' => 'Paket update',
            'description' => 'Deskripsi update',
            'price' => 300000,
            'duration' => '5 jam',
            'capacity' => 10,
            'status' => 'aktif',
        ]);
        $otherDestination = Destination::query()->create([
            'name' => 'Gili Update',
            'slug' => 'gili-update',
            'description' => 'Spot untuk update reservasi.',
            'difficulty' => 'mudah',
            'status' => 'aktif',
        ]);
        $otherPackage->destinations()->sync([$otherDestination->id]);

        $otherSchedule = Schedule::query()->create([
            'snorkeling_package_id' => $otherPackage->id,
            'start_at' => now()->addDays(4),
            'end_at' => now()->addDays(4)->addHours(5),
            'capacity' => 10,
            'boat_count' => 2,
            'booked_count' => 0,
            'status' => 'tersedia',
        ]);

        $reservation = Reservation::query()->create([
            'code' => 'CBR-TEST-EDIT',
            'user_id' => $customer->id,
            'snorkeling_package_id' => $package->id,
            'schedule_id' => $schedule->id,
            'booking_date' => $schedule->start_at->toDateString(),
            'participants' => 2,
            'contact_name' => 'Customer',
            'contact_phone' => '081200000008',
            'pickup_location' => 'Pelabuhan',
            'total_price' => 250000,
            'status' => 'menunggu_pembayaran',
        ]);

        Payment::query()->create([
            'reservation_id' => $reservation->id,
            'amount' => 250000,
            'method' => 'transfer_bank',
            'status' => 'belum_bayar',
        ]);

        $schedule->update(['boat_count' => 1, 'booked_count' => 1, 'status' => 'penuh']);

        $response = $this->actingAs($customer)->put(route('customer.reservations.update', $reservation), [
            'snorkeling_package_id' => $otherPackage->id,
            'destination_id' => $otherDestination->id,
            'schedule_id' => $otherSchedule->id,
            'booking_date' => $otherSchedule->start_at->toDateString(),
            'contact_name' => 'Customer Update',
            'contact_phone' => '081200000009',
            'adult_count' => 2,
            'child_count' => 1,
            'pickup_location' => 'Hotel',
            'notes' => 'Pindah jadwal',
        ]);

        $response->assertRedirect(route('customer.reservations.show', $reservation));
        $this->assertDatabaseHas('reservations', [
            'id' => $reservation->id,
            'snorkeling_package_id' => $otherPackage->id,
            'schedule_id' => $otherSchedule->id,
            'participants' => 3,
            'adult_count' => 2,
            'child_count' => 1,
            'contact_name' => 'Customer Update',
            'total_price' => 300000,
        ]);
        $this->assertDatabaseHas('payments', [
            'reservation_id' => $reservation->id,
            'amount' => 300000,
        ]);
        $this->assertDatabaseHas('schedules', [
            'id' => $schedule->id,
            'booked_count' => 0,
            'status' => 'tersedia',
        ]);
        $this->assertDatabaseHas('schedules', [
            'id' => $otherSchedule->id,
            'booked_count' => 1,
        ]);
    }

    public function test_customer_can_delete_reservation_before_sending_payment_proof(): void
    {
        $customer = User::factory()->create([
            'role' => 'customer',
        ]);

        [$package, $schedule] = $this->createPackageAndSchedule();

        $reservation = Reservation::query()->create([
            'code' => 'CBR-TEST-DELETE',
            'user_id' => $customer->id,
            'snorkeling_package_id' => $package->id,
            'schedule_id' => $schedule->id,
            'booking_date' => $schedule->start_at->toDateString(),
            'participants' => 2,
            'contact_name' => 'Customer',
            'contact_phone' => '081200000010',
            'pickup_location' => 'Pelabuhan',
            'total_price' => 250000,
            'status' => 'menunggu_pembayaran',
        ]);

        Payment::query()->create([
            'reservation_id' => $reservation->id,
            'amount' => 250000,
            'method' => 'transfer_bank',
            'status' => 'belum_bayar',
        ]);

        $schedule->update(['boat_count' => 1, 'booked_count' => 1, 'status' => 'penuh']);

        $response = $this->actingAs($customer)->delete(route('customer.reservations.destroy', $reservation));

        $response->assertRedirect(route('customer.reservations.index'));
        $this->assertDatabaseMissing('reservations', [
            'id' => $reservation->id,
        ]);
        $this->assertDatabaseMissing('payments', [
            'reservation_id' => $reservation->id,
        ]);
        $this->assertDatabaseHas('schedules', [
            'id' => $schedule->id,
            'booked_count' => 0,
            'status' => 'tersedia',
        ]);
    }

    public function test_customer_cannot_update_or_delete_reservation_after_payment_proof_is_submitted(): void
    {
        $customer = User::factory()->create([
            'role' => 'customer',
        ]);

        [$package, $schedule] = $this->createPackageAndSchedule();

        $reservation = Reservation::query()->create([
            'code' => 'CBR-TEST-LOCK',
            'user_id' => $customer->id,
            'snorkeling_package_id' => $package->id,
            'schedule_id' => $schedule->id,
            'booking_date' => $schedule->start_at->toDateString(),
            'participants' => 1,
            'contact_name' => 'Customer',
            'contact_phone' => '081200000011',
            'pickup_location' => 'Pelabuhan',
            'total_price' => 250000,
            'status' => 'menunggu_verifikasi',
        ]);

        Payment::query()->create([
            'reservation_id' => $reservation->id,
            'amount' => 250000,
            'method' => 'transfer_bank',
            'status' => 'menunggu_verifikasi',
            'proof_image' => 'storage/payments/bukti.png',
        ]);

        $this->actingAs($customer)
            ->get(route('customer.reservations.edit', $reservation))
            ->assertForbidden();

        $this->actingAs($customer)
            ->put(route('customer.reservations.update', $reservation), [
                'snorkeling_package_id' => $package->id,
                'destination_id' => $package->destinations()->firstOrFail()->id,
                'schedule_id' => $schedule->id,
                'booking_date' => $schedule->start_at->toDateString(),
                'contact_name' => 'Tidak Boleh',
                'contact_phone' => '081200000012',
                'adult_count' => 1,
                'child_count' => 0,
            ])
            ->assertForbidden();

        $this->actingAs($customer)
            ->delete(route('customer.reservations.destroy', $reservation))
            ->assertForbidden();
    }

    public function test_customer_reservation_detail_shows_inline_payment_form_when_payment_is_pending(): void
    {
        $customer = User::factory()->create([
            'role' => 'customer',
        ]);

        [$package, $schedule] = $this->createPackageAndSchedule();

        $reservation = Reservation::query()->create([
            'code' => 'CBR-TEST-DETAIL',
            'user_id' => $customer->id,
            'snorkeling_package_id' => $package->id,
            'schedule_id' => $schedule->id,
            'booking_date' => $schedule->start_at->toDateString(),
            'participants' => 1,
            'contact_name' => 'Customer',
            'contact_phone' => '081200000006',
            'pickup_location' => 'Pelabuhan',
            'total_price' => 250000,
            'status' => 'menunggu_pembayaran',
        ]);

        Payment::query()->create([
            'reservation_id' => $reservation->id,
            'amount' => 250000,
            'method' => 'transfer_bank',
            'status' => 'belum_bayar',
        ]);

        $response = $this->actingAs($customer)->get(route('customer.reservations.show', $reservation));

        $response->assertOk();
        $response->assertSee('Ringkasan Reservasi');
        $response->assertSee('Upload Bukti Pembayaran');
        $response->assertSee('Bukti Transfer');
        $response->assertSee(route('customer.reservations.payment.store', $reservation), false);
        $response->assertDontSee('Buka Halaman Pembayaran');
        }

    public function test_reservation_success_page_offers_direct_link_to_payment_upload(): void
    {
        $customer = User::factory()->create([
            'role' => 'customer',
            'phone' => '081200000007',
        ]);

        [$package, $schedule] = $this->createPackageAndSchedule();

        $response = $this->actingAs($customer)->post(route('reservations.store'), [
            'snorkeling_package_id' => $package->id,
            'destination_id' => $package->destinations()->firstOrFail()->id,
            'schedule_id' => $schedule->id,
            'booking_date' => $schedule->start_at->toDateString(),
            'contact_name' => 'Customer Reservasi',
            'contact_phone' => '081200000007',
            'adult_count' => 1,
            'child_count' => 0,
            'pickup_location' => 'Pelabuhan Senggigi',
            'notes' => 'Mohon info jam kumpul',
        ]);

        $response->assertRedirect(route('reservations.success'));

        $reservation = Reservation::query()->latest('id')->firstOrFail();

        $this->actingAs($customer)
            ->withSession(['reservation_code' => $reservation->code])
            ->get(route('reservations.success'))
            ->assertOk()
            ->assertSee('Lanjut ke Detail Reservasi')
            ->assertSee(route('customer.reservations.show', $reservation).'#pembayaran', false);
    }

    public function test_customer_can_create_review_from_completed_reservation(): void
    {
        $customer = User::factory()->create([
            'role' => 'customer',
        ]);

        [$package, $schedule] = $this->createPackageAndSchedule();

        $reservation = Reservation::query()->create([
            'code' => 'CBR-TEST-002',
            'user_id' => $customer->id,
            'snorkeling_package_id' => $package->id,
            'schedule_id' => $schedule->id,
            'booking_date' => $schedule->start_at->toDateString(),
            'participants' => 1,
            'contact_name' => 'Customer',
            'contact_phone' => '081200000003',
            'pickup_location' => 'Pelabuhan',
            'total_price' => 250000,
            'status' => 'selesai',
        ]);

        $response = $this->actingAs($customer)->post(route('customer.reviews.store'), [
            'reservation_id' => $reservation->id,
            'rating' => 5,
            'comment' => 'Pelayanan sangat baik',
        ]);

        $response->assertRedirect(route('customer.reviews.index'));
        $this->assertDatabaseHas('reviews', [
            'reservation_id' => $reservation->id,
            'user_id' => $customer->id,
            'status' => 'draft',
        ]);
    }

    public function test_customer_can_update_profile(): void
    {
        $customer = User::factory()->create([
            'role' => 'customer',
            'email' => 'old@example.com',
        ]);

        $response = $this->actingAs($customer)->put(route('customer.profile.update'), [
            'name' => 'Nama Baru',
            'email' => 'baru@example.com',
            'phone' => '081299999999',
            'address' => 'Alamat baru',
        ]);

        $response->assertRedirect(route('customer.profile.index'));
        $this->assertDatabaseHas('users', [
            'id' => $customer->id,
            'name' => 'Nama Baru',
            'email' => 'baru@example.com',
            'phone' => '081299999999',
            'address' => 'Alamat baru',
        ]);
    }

    public function test_admin_can_reply_to_customer_message(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $customer = User::factory()->create([
            'role' => 'customer',
        ]);

        $complaint = Complaint::query()->create([
            'user_id' => $customer->id,
            'subject' => 'Pertanyaan jadwal',
            'message' => 'Apakah masih ada slot untuk besok?',
            'status' => 'baru',
        ]);

        $response = $this->actingAs($admin)->put(route('admin.complaints.update', $complaint), [
            'status' => 'diproses',
            'admin_reply' => 'Masih tersedia, silakan lanjut reservasi melalui halaman paket.',
        ]);

        $response->assertRedirect(route('admin.complaints.show', $complaint));
        $this->assertDatabaseHas('complaints', [
            'id' => $complaint->id,
            'status' => 'diproses',
            'admin_reply' => 'Masih tersedia, silakan lanjut reservasi melalui halaman paket.',
        ]);
        $this->assertNotNull($complaint->fresh()?->replied_at);
    }

    public function test_customer_can_view_own_message_and_admin_reply(): void
    {
        $customer = User::factory()->create([
            'role' => 'customer',
        ]);

        $message = Complaint::query()->create([
            'user_id' => $customer->id,
            'subject' => 'Minta informasi',
            'message' => 'Apakah ada paket keluarga?',
            'admin_reply' => 'Ada, silakan lihat Paket Privat Keluarga di halaman paket.',
            'replied_at' => now(),
            'status' => 'selesai',
        ]);

        $response = $this->actingAs($customer)->get(route('customer.messages.show', $message));

        $response->assertOk();
        $response->assertSee('Minta informasi');
        $response->assertSee('Ada, silakan lihat Paket Privat Keluarga di halaman paket.');
    }

    public function test_customer_cannot_view_other_customers_message(): void
    {
        $customer = User::factory()->create([
            'role' => 'customer',
        ]);

        $otherCustomer = User::factory()->create([
            'role' => 'customer',
        ]);

        $message = Complaint::query()->create([
            'user_id' => $otherCustomer->id,
            'subject' => 'Pesan orang lain',
            'message' => 'Ini bukan milik saya.',
            'status' => 'baru',
        ]);

        $this->actingAs($customer)
            ->get(route('customer.messages.show', $message))
            ->assertForbidden();
    }

    public function test_admin_can_filter_reservations_by_code(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $customer = User::factory()->create([
            'role' => 'customer',
        ]);

        [$package, $schedule] = $this->createPackageAndSchedule();

        Reservation::query()->create([
            'code' => 'CBR-FILTER-001',
            'user_id' => $customer->id,
            'snorkeling_package_id' => $package->id,
            'schedule_id' => $schedule->id,
            'booking_date' => $schedule->start_at->toDateString(),
            'participants' => 1,
            'contact_name' => 'Customer',
            'contact_phone' => '081200000004',
            'pickup_location' => 'Pelabuhan',
            'total_price' => 250000,
            'status' => 'menunggu_pembayaran',
        ]);

        Reservation::query()->create([
            'code' => 'CBR-LAIN-002',
            'user_id' => $customer->id,
            'snorkeling_package_id' => $package->id,
            'schedule_id' => $schedule->id,
            'booking_date' => $schedule->start_at->toDateString(),
            'participants' => 1,
            'contact_name' => 'Customer',
            'contact_phone' => '081200000005',
            'pickup_location' => 'Pelabuhan',
            'total_price' => 250000,
            'status' => 'menunggu_pembayaran',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.reservations.index', ['q' => 'FILTER']));

        $response->assertOk();
        $response->assertSee('CBR-FILTER-001');
        $response->assertDontSee('CBR-LAIN-002');
    }

    private function createPackageAndSchedule(): array
    {
        $package = SnorkelingPackage::query()->create([
            'name' => 'Paket Reservasi',
            'slug' => 'paket-reservasi',
            'short_description' => 'Paket tes reservasi',
            'description' => 'Deskripsi tes reservasi',
            'price' => 250000,
            'duration' => '4 jam',
            'capacity' => 8,
            'status' => 'aktif',
        ]);
        $destination = Destination::query()->create([
            'name' => 'Gili Nanggu',
            'slug' => 'gili-nanggu',
            'description' => 'Spot tes reservasi.',
            'difficulty' => 'mudah',
            'status' => 'aktif',
        ]);
        $package->destinations()->sync([$destination->id]);

        $schedule = Schedule::query()->create([
            'snorkeling_package_id' => $package->id,
            'start_at' => now()->addDays(3)->setTime(8, 0),
            'end_at' => now()->addDays(3)->setTime(12, 0),
            'capacity' => 8,
            'boat_count' => 3,
            'booked_count' => 0,
            'status' => 'tersedia',
        ]);

        return [$package, $schedule];
    }
}
