<?php

namespace Tests\Feature;

use App\Models\Destination;
use App\Models\Faq;
use App\Models\GalleryItem;
use App\Models\Payment;
use App\Models\Reservation;
use App\Models\Review;
use App\Models\Schedule;
use App\Models\SnorkelingPackage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_delete_gallery_item(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $galleryItem = GalleryItem::query()->create([
            'title' => 'Foto Dummy',
            'image_path' => 'images/site/hero-ocean.svg',
            'category' => 'snorkeling',
            'caption' => 'Caption dummy',
            'is_featured' => false,
        ]);

        $response = $this->actingAs($admin)->delete(route('admin.gallery.destroy', $galleryItem));

        $response->assertRedirect(route('admin.gallery.index'));
        $this->assertDatabaseMissing('gallery_items', [
            'id' => $galleryItem->id,
        ]);
    }

    public function test_admin_cannot_delete_destination_that_is_still_used_by_package(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $destination = Destination::query()->create([
            'name' => 'Spot Dummy',
            'slug' => 'spot-dummy',
            'description' => 'Deskripsi dummy',
            'difficulty' => 'mudah',
            'status' => 'aktif',
        ]);

        $package = SnorkelingPackage::query()->create([
            'name' => 'Paket Dummy',
            'slug' => 'paket-dummy',
            'short_description' => 'Singkat',
            'description' => 'Deskripsi paket',
            'price' => 100000,
            'duration' => '3 jam',
            'capacity' => 5,
            'status' => 'aktif',
        ]);

        $package->destinations()->sync([$destination->id]);

        $response = $this->actingAs($admin)->delete(route('admin.destinations.destroy', $destination));

        $response->assertRedirect(route('admin.destinations.index'));
        $this->assertDatabaseHas('destinations', [
            'id' => $destination->id,
        ]);
    }

    public function test_admin_can_create_and_delete_faq(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $storeResponse = $this->actingAs($admin)->post(route('admin.faqs.store'), [
            'question' => 'Apakah tersedia life jacket?',
            'answer' => 'Ya, tersedia untuk setiap peserta.',
            'sort_order' => 1,
        ]);

        $storeResponse->assertRedirect(route('admin.faqs.index'));
        $this->assertDatabaseHas('faqs', [
            'question' => 'Apakah tersedia life jacket?',
        ]);

        $faq = Faq::query()->where('question', 'Apakah tersedia life jacket?')->firstOrFail();

        $deleteResponse = $this->actingAs($admin)->delete(route('admin.faqs.destroy', $faq));

        $deleteResponse->assertRedirect(route('admin.faqs.index'));
        $this->assertDatabaseMissing('faqs', [
            'id' => $faq->id,
        ]);
    }

    public function test_master_admin_user_management_only_handles_admin_accounts(): void
    {
        $masterAdmin = User::factory()->create([
            'role' => 'admin',
            'is_master_admin' => true,
        ]);

        User::factory()->create([
            'name' => 'Admin Operasional',
            'email' => 'admin.operasional@example.com',
            'role' => 'admin',
        ]);

        User::factory()->create([
            'name' => 'Pelanggan Campur',
            'email' => 'pelanggan.campur@example.com',
            'role' => 'customer',
        ]);

        $indexResponse = $this->actingAs($masterAdmin)->get(route('admin.users.index'));

        $indexResponse->assertOk();
        $indexResponse->assertSee('Admin Operasional');
        $indexResponse->assertDontSee('Pelanggan Campur');

        $storeResponse = $this->actingAs($masterAdmin)->post(route('admin.users.store'), [
            'name' => 'Admin Baru',
            'email' => 'admin.baru@example.com',
            'phone' => '08123456001',
            'address' => 'Kantor',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $storeResponse->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseHas('users', [
            'email' => 'admin.baru@example.com',
            'role' => 'admin',
        ]);
    }

    public function test_admin_can_create_and_update_customer_from_customer_module(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $storeResponse = $this->actingAs($admin)->post(route('admin.customers.store'), [
            'name' => 'Pelanggan Admin Input',
            'email' => 'pelanggan.input@example.com',
            'phone' => '08123456002',
            'address' => 'Jl. Laut',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $storeResponse->assertRedirect(route('admin.customers.index'));
        $this->assertDatabaseHas('users', [
            'email' => 'pelanggan.input@example.com',
            'role' => 'customer',
        ]);

        $customer = User::query()->where('email', 'pelanggan.input@example.com')->firstOrFail();

        $updateResponse = $this->actingAs($admin)->put(route('admin.customers.update', $customer), [
            'name' => 'Pelanggan Update',
            'email' => 'pelanggan.input@example.com',
            'phone' => '08123456003',
            'address' => 'Jl. Karang',
            'password' => '',
            'password_confirmation' => '',
        ]);

        $updateResponse->assertRedirect(route('admin.customers.index'));
        $this->assertDatabaseHas('users', [
            'id' => $customer->id,
            'name' => 'Pelanggan Update',
            'role' => 'customer',
        ]);
    }

    public function test_admin_can_mark_confirmed_reservation_as_finished(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $reservation = $this->createReservation();
        $reservation->update([
            'status' => 'terkonfirmasi',
        ]);

        $response = $this->actingAs($admin)->put(route('admin.reservations.update', $reservation), [
            'notes' => 'Trip snorkeling sudah selesai dijalankan.',
        ]);

        $response->assertRedirect(route('admin.reservations.show', $reservation));
        $this->assertDatabaseHas('reservations', [
            'id' => $reservation->id,
            'status' => 'selesai',
            'notes' => 'Trip snorkeling sudah selesai dijalankan.',
        ]);
    }

    public function test_admin_dashboard_displays_reservation_counts_by_status(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->createReservation('menunggu_verifikasi');
        $this->createReservation('terkonfirmasi');
        $this->createReservation('menunggu_pembayaran');
        $this->createReservation('selesai');

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSeeTextInOrder([
            'Total Reservasi',
            '4',
            'Menunggu Konfirmasi',
            '1',
            'Terkonfirmasi',
            '1',
            'Menunggu Bayar',
            '1',
        ]);
    }

    public function test_admin_can_verify_payment_and_sync_reservation_status(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $reservation = $this->createReservation();
        $payment = Payment::query()->create([
            'reservation_id' => $reservation->id,
            'amount' => 250000,
            'method' => 'transfer_bank',
            'status' => 'menunggu_verifikasi',
        ]);

        $response = $this->actingAs($admin)->put(route('admin.payments.update', $payment), [
            'status' => 'diterima',
            'notes' => 'Bukti transfer valid.',
        ]);

        $response->assertRedirect(route('admin.payments.show', $payment));
        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'diterima',
            'notes' => 'Bukti transfer valid.',
        ]);
        $this->assertDatabaseHas('reservations', [
            'id' => $reservation->id,
            'status' => 'terkonfirmasi',
        ]);
    }

    public function test_admin_can_update_review_status_and_delete_complaint(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $reservation = $this->createReservation();

        $review = Review::query()->create([
            'reservation_id' => $reservation->id,
            'user_id' => $reservation->user_id,
            'snorkeling_package_id' => $reservation->snorkeling_package_id,
            'rating' => 5,
            'comment' => 'Bagus sekali',
            'status' => 'published',
        ]);

        $complaint = \App\Models\Complaint::query()->create([
            'user_id' => $reservation->user_id,
            'reservation_id' => $reservation->id,
            'subject' => 'Pertanyaan keberangkatan',
            'message' => 'Jam kumpulnya jam berapa?',
            'status' => 'baru',
        ]);

        $reviewResponse = $this->actingAs($admin)->put(route('admin.reviews.update', $review), [
            'status' => 'hidden',
        ]);

        $reviewResponse->assertRedirect(route('admin.reviews.show', $review));
        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'status' => 'hidden',
        ]);

        $complaintResponse = $this->actingAs($admin)->delete(route('admin.complaints.destroy', $complaint));

        $complaintResponse->assertRedirect(route('admin.complaints.index'));
        $this->assertDatabaseMissing('complaints', [
            'id' => $complaint->id,
        ]);
    }

    public function test_admin_cannot_mark_unconfirmed_reservation_as_finished(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $reservation = $this->createReservation();

        $response = $this->actingAs($admin)->put(route('admin.reservations.update', $reservation), [
            'notes' => 'Mencoba menyelesaikan terlalu cepat.',
        ]);

        $response->assertSessionHasErrors('notes');
        $this->assertDatabaseHas('reservations', [
            'id' => $reservation->id,
            'status' => 'menunggu_verifikasi',
        ]);
    }

    public function test_admin_can_filter_schedules_by_package_date_and_status(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $matchingPackage = SnorkelingPackage::query()->create([
            'name' => 'Lembongan Morning Escape',
            'slug' => 'lembongan-morning-escape',
            'short_description' => 'Trip pagi',
            'description' => 'Trip pagi yang cocok untuk pengujian filter.',
            'price' => 450000,
            'duration' => '4 jam',
            'capacity' => 8,
            'status' => 'aktif',
        ]);

        $otherPackage = SnorkelingPackage::query()->create([
            'name' => 'Adventure Reef Run',
            'slug' => 'adventure-reef-run',
            'short_description' => 'Trip siang',
            'description' => 'Trip siang yang tidak boleh lolos filter.',
            'price' => 520000,
            'duration' => '4 jam',
            'capacity' => 8,
            'status' => 'aktif',
        ]);

        Schedule::query()->create([
            'snorkeling_package_id' => $matchingPackage->id,
            'start_at' => now()->addDays(5)->setTime(8, 0),
            'end_at' => now()->addDays(5)->setTime(12, 0),
            'capacity' => 8,
            'boat_count' => 3,
            'booked_count' => 0,
            'status' => 'tersedia',
        ]);

        Schedule::query()->create([
            'snorkeling_package_id' => $otherPackage->id,
            'start_at' => now()->addDays(5)->setTime(9, 0),
            'end_at' => now()->addDays(5)->setTime(13, 0),
            'capacity' => 8,
            'boat_count' => 3,
            'booked_count' => 3,
            'status' => 'penuh',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.schedules.index', [
            'q' => 'Lembongan Morning',
            'date' => now()->addDays(5)->toDateString(),
            'status' => 'tersedia',
        ]));

        $response->assertOk();
        $response->assertSee('Lembongan Morning Escape');
        $response->assertDontSee('Adventure Reef Run');
    }

    public function test_admin_can_filter_reservations_by_code_customer_package_and_destination(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $matchingDestination = Destination::query()->create([
            'name' => 'Crystal Bay',
            'slug' => 'crystal-bay',
            'description' => 'Spot utama untuk pengujian filter reservasi.',
            'difficulty' => 'mudah',
            'status' => 'aktif',
        ]);

        $otherDestination = Destination::query()->create([
            'name' => 'Wall Point',
            'slug' => 'wall-point',
            'description' => 'Spot pembanding untuk pengujian filter reservasi.',
            'difficulty' => 'menengah',
            'status' => 'aktif',
        ]);

        $matchingReservation = $this->createReservation('menunggu_verifikasi');
        $matchingReservation->user->update(['name' => 'Santa']);
        $matchingReservation->package->update([
            'name' => 'Lembongan Morning Escape',
            'slug' => 'lembongan-morning-escape-filter',
        ]);
        $matchingReservation->update([
            'code' => 'CBR-MATCH-001',
            'destination_id' => $matchingDestination->id,
        ]);

        $otherReservation = $this->createReservation('terkonfirmasi');
        $otherReservation->user->update(['name' => 'Xander']);
        $otherReservation->package->update([
            'name' => 'Adventure Reef Run',
            'slug' => 'adventure-reef-run-filter',
        ]);
        $otherReservation->update([
            'code' => 'CBR-OTHER-001',
            'destination_id' => $otherDestination->id,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.reservations.index', [
            'code' => 'CBR-MATCH',
            'customer' => 'Santa',
            'package' => 'Morning Escape',
            'destination' => 'Crystal',
        ]));

        $response->assertOk();
        $response->assertSee('CBR-MATCH-001');
        $response->assertDontSee('CBR-OTHER-001');
    }

    public function test_admin_can_filter_payments_by_code_customer_status_and_date(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $matchingReservation = $this->createReservation('menunggu_verifikasi');
        $matchingReservation->user->update(['name' => 'Santa']);
        $matchingReservation->update(['code' => 'CBR-PAY-001']);

        $matchingPayment = Payment::query()->create([
            'reservation_id' => $matchingReservation->id,
            'amount' => 450000,
            'method' => 'transfer_bank',
            'status' => 'menunggu_verifikasi',
        ]);
        $matchingPayment->timestamps = false;
        $matchingPayment->created_at = now()->subDay()->setTime(8, 30);
        $matchingPayment->updated_at = now()->subDay()->setTime(8, 30);
        $matchingPayment->save();

        $otherReservation = $this->createReservation('terkonfirmasi');
        $otherReservation->user->update(['name' => 'Xander']);
        $otherReservation->update(['code' => 'CBR-PAY-999']);

        $otherPayment = Payment::query()->create([
            'reservation_id' => $otherReservation->id,
            'amount' => 980000,
            'method' => 'transfer_bank',
            'status' => 'diterima',
        ]);
        $otherPayment->timestamps = false;
        $otherPayment->created_at = now()->setTime(9, 0);
        $otherPayment->updated_at = now()->setTime(9, 0);
        $otherPayment->save();

        $response = $this->actingAs($admin)->get(route('admin.payments.index', [
            'code' => 'CBR-PAY-001',
            'customer' => 'Santa',
            'status' => 'menunggu_verifikasi',
            'date' => now()->subDay()->toDateString(),
        ]));

        $response->assertOk();
        $response->assertSee('CBR-PAY-001');
        $response->assertDontSee('CBR-PAY-999');
    }

    public function test_admin_can_filter_customers_by_name(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        User::factory()->create([
            'name' => 'Santa Customer',
            'email' => 'santa.customer@example.com',
            'role' => 'customer',
        ]);

        User::factory()->create([
            'name' => 'Xander Customer',
            'email' => 'xander.customer@example.com',
            'role' => 'customer',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.customers.index', [
            'name' => 'Santa',
        ]));

        $response->assertOk();
        $response->assertSee('Santa Customer');
        $response->assertDontSee('Xander Customer');
    }

    private function createReservation(string $status = 'menunggu_verifikasi'): Reservation
    {
        $sequence = Reservation::count() + 1;

        $customer = User::factory()->create([
            'role' => 'customer',
        ]);

        $package = SnorkelingPackage::query()->create([
            'name' => 'Paket Tes '.$sequence,
            'slug' => 'paket-tes-'.$sequence,
            'short_description' => 'Tes',
            'description' => 'Deskripsi tes',
            'price' => 250000,
            'duration' => '4 jam',
            'capacity' => 6,
            'status' => 'aktif',
        ]);

        return Reservation::query()->create([
            'code' => 'RSV-'.str_pad((string) $sequence, 3, '0', STR_PAD_LEFT),
            'user_id' => $customer->id,
            'snorkeling_package_id' => $package->id,
            'booking_date' => now()->toDateString(),
            'participants' => 2,
            'contact_name' => 'Customer Tes',
            'contact_phone' => '08123456789',
            'pickup_location' => 'Pelabuhan',
            'total_price' => 250000,
            'status' => $status,
        ]);
    }
}
