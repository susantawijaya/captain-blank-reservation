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

    public function test_admin_can_reorder_schedules_using_single_schedule_order_filter(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $alphaPackage = SnorkelingPackage::query()->create([
            'name' => 'Alpha Morning',
            'slug' => 'alpha-morning',
            'short_description' => 'Trip pagi',
            'description' => 'Trip pagi untuk pengujian urutan.',
            'price' => 400000,
            'duration' => '4 jam',
            'capacity' => 8,
            'status' => 'aktif',
        ]);

        $zetaPackage = SnorkelingPackage::query()->create([
            'name' => 'Zeta Sunset',
            'slug' => 'zeta-sunset',
            'short_description' => 'Trip sore',
            'description' => 'Trip sore untuk pengujian urutan.',
            'price' => 500000,
            'duration' => '4 jam',
            'capacity' => 8,
            'status' => 'aktif',
        ]);

        Schedule::query()->create([
            'snorkeling_package_id' => $zetaPackage->id,
            'start_at' => now()->addDays(3)->setTime(14, 0),
            'end_at' => now()->addDays(3)->setTime(18, 0),
            'capacity' => 8,
            'boat_count' => 2,
            'booked_count' => 2,
            'status' => 'penuh',
        ]);

        Schedule::query()->create([
            'snorkeling_package_id' => $alphaPackage->id,
            'start_at' => now()->addDays(1)->setTime(8, 0),
            'end_at' => now()->addDays(1)->setTime(12, 0),
            'capacity' => 8,
            'boat_count' => 3,
            'booked_count' => 1,
            'status' => 'tersedia',
        ]);

        Schedule::query()->create([
            'snorkeling_package_id' => $alphaPackage->id,
            'start_at' => now()->addDays(2)->setTime(10, 0),
            'end_at' => now()->addDays(2)->setTime(14, 0),
            'capacity' => 8,
            'boat_count' => 2,
            'booked_count' => 1,
            'status' => 'tersedia',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.schedules.index', [
            'schedule_order' => 'availability',
        ]));

        $response->assertOk();
        $response->assertSee('Alpha Morning');
        $response->assertSee('Hampir Penuh');
        $response->assertSee('Zeta Sunset');
        $response->assertSee('Penuh');
    }

    private function createReservation(): Reservation
    {
        $customer = User::factory()->create([
            'role' => 'customer',
        ]);

        $package = SnorkelingPackage::query()->create([
            'name' => 'Paket Tes',
            'slug' => 'paket-tes',
            'short_description' => 'Tes',
            'description' => 'Deskripsi tes',
            'price' => 250000,
            'duration' => '4 jam',
            'capacity' => 6,
            'status' => 'aktif',
        ]);

        return Reservation::query()->create([
            'code' => 'RSV-001',
            'user_id' => $customer->id,
            'snorkeling_package_id' => $package->id,
            'booking_date' => now()->toDateString(),
            'participants' => 2,
            'contact_name' => 'Customer Tes',
            'contact_phone' => '08123456789',
            'pickup_location' => 'Pelabuhan',
            'total_price' => 250000,
            'status' => 'menunggu_verifikasi',
        ]);
    }
}
