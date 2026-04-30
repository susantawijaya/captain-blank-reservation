<?php

namespace Tests\Feature;

use App\Models\Destination;
use App\Models\Reservation;
use App\Models\Review;
use App\Models\SnorkelingPackage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicListingFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_destination_page_can_filter_by_search_keyword(): void
    {
        Destination::query()->create([
            'name' => 'Gili Nanggu',
            'slug' => 'gili-nanggu',
            'description' => 'Perairan tenang untuk snorkeling santai.',
            'difficulty' => 'mudah',
            'status' => 'aktif',
        ]);

        Destination::query()->create([
            'name' => 'Turtle Point',
            'slug' => 'turtle-point',
            'description' => 'Spot penyu untuk trip privat.',
            'difficulty' => 'menengah',
            'status' => 'aktif',
        ]);

        $response = $this->get(route('destinations.index', ['q' => 'nanggu']));

        $response->assertOk();
        $response->assertSee('Gili Nanggu');
        $response->assertDontSee('Turtle Point');
    }

    public function test_destination_page_can_filter_by_difficulty(): void
    {
        Destination::query()->create([
            'name' => 'Spot Mudah',
            'slug' => 'spot-mudah',
            'description' => 'Untuk pemula.',
            'difficulty' => 'mudah',
            'status' => 'aktif',
        ]);

        Destination::query()->create([
            'name' => 'Spot Lanjutan',
            'slug' => 'spot-lanjutan',
            'description' => 'Untuk yang berpengalaman.',
            'difficulty' => 'lanjutan',
            'status' => 'aktif',
        ]);

        $response = $this->get(route('destinations.index', ['difficulty' => 'lanjutan']));

        $response->assertOk();
        $response->assertSee('Spot Lanjutan');
        $response->assertDontSee('Spot Mudah');
    }

    public function test_clicking_destination_context_can_filter_packages_to_that_destination(): void
    {
        $giliManggu = Destination::query()->create([
            'name' => 'Gili Manggu',
            'slug' => 'gili-manggu',
            'description' => 'Air jernih dan tenang.',
            'difficulty' => 'mudah',
            'status' => 'aktif',
        ]);

        $giliSudak = Destination::query()->create([
            'name' => 'Gili Sudak',
            'slug' => 'gili-sudak',
            'description' => 'Spot lain.',
            'difficulty' => 'mudah',
            'status' => 'aktif',
        ]);

        $mangguPackage = SnorkelingPackage::query()->create([
            'name' => 'Paket Manggu',
            'slug' => 'paket-manggu',
            'short_description' => 'Khusus Manggu',
            'description' => 'Trip ke Manggu.',
            'price' => 350000,
            'duration' => '5 jam',
            'capacity' => 8,
            'status' => 'aktif',
        ]);
        $mangguPackage->destinations()->sync([$giliManggu->id]);

        $sudakPackage = SnorkelingPackage::query()->create([
            'name' => 'Paket Sudak',
            'slug' => 'paket-sudak',
            'short_description' => 'Khusus Sudak',
            'description' => 'Trip ke Sudak.',
            'price' => 350000,
            'duration' => '5 jam',
            'capacity' => 8,
            'status' => 'aktif',
        ]);
        $sudakPackage->destinations()->sync([$giliSudak->id]);

        $response = $this->get(route('packages.index', ['destination' => $giliManggu->id]));

        $response->assertOk();
        $response->assertSee('Paket Manggu');
        $response->assertDontSee('Paket Sudak');
        $response->assertSee('Gili Manggu');
    }

    public function test_jadwal_route_redirects_to_package_page(): void
    {
        $response = $this->get(route('schedules.index'));

        $response->assertRedirect(route('packages.index'));
    }

    public function test_package_page_shows_sold_out_indicator_when_all_slots_are_full(): void
    {
        $fullPackage = SnorkelingPackage::query()->create([
            'name' => 'Paket Penuh',
            'slug' => 'paket-penuh',
            'short_description' => 'Sudah penuh',
            'description' => 'Trip penuh.',
            'price' => 350000,
            'duration' => '5 jam',
            'capacity' => 8,
            'status' => 'aktif',
        ]);

        \App\Models\Schedule::query()->create([
            'snorkeling_package_id' => $fullPackage->id,
            'start_at' => now()->addDays(5),
            'end_at' => now()->addDays(5)->addHours(5),
            'capacity' => 8,
            'boat_count' => 1,
            'booked_count' => 1,
            'status' => 'penuh',
        ]);

        $response = $this->get(route('packages.index'));

        $response->assertOk();
        $response->assertSee('Paket Penuh');
        $response->assertSee('Habis');
        $response->assertSee('Semua kapal pada jadwal aktif saat ini sudah terpakai.');
    }

    public function test_public_review_page_shows_package_and_destination_in_review_card(): void
    {
        $customer = User::factory()->create([
            'name' => 'Santa',
            'role' => 'customer',
        ]);

        $destination = Destination::query()->create([
            'name' => 'Crystal Bay',
            'slug' => 'crystal-bay-review',
            'description' => 'Spot untuk review publik.',
            'difficulty' => 'mudah',
            'status' => 'aktif',
        ]);

        $package = SnorkelingPackage::query()->create([
            'name' => 'Lembongan Morning Escape',
            'slug' => 'lembongan-morning-escape-review',
            'short_description' => 'Trip pagi.',
            'description' => 'Trip pagi untuk pengujian review.',
            'price' => 450000,
            'duration' => '4 jam',
            'capacity' => 8,
            'status' => 'aktif',
        ]);

        $package->destinations()->sync([$destination->id]);

        $reservation = Reservation::query()->create([
            'code' => 'RVW-SHOW-001',
            'user_id' => $customer->id,
            'snorkeling_package_id' => $package->id,
            'destination_id' => $destination->id,
            'booking_date' => now()->toDateString(),
            'participants' => 2,
            'contact_name' => 'Santa',
            'contact_phone' => '08123456789',
            'pickup_location' => 'Pelabuhan',
            'total_price' => 450000,
            'status' => 'selesai',
        ]);

        Review::query()->create([
            'reservation_id' => $reservation->id,
            'user_id' => $customer->id,
            'snorkeling_package_id' => $package->id,
            'rating' => 5,
            'comment' => 'Tripnya rapi dan menyenangkan.',
            'status' => 'published',
        ]);

        $response = $this->get(route('reviews.index'));

        $response->assertOk();
        $response->assertSee('Lembongan Morning Escape');
        $response->assertSee('Crystal Bay');
    }

    public function test_public_review_page_can_filter_by_package_destination_rating_and_order(): void
    {
        $matchingCustomer = User::factory()->create([
            'name' => 'Santa',
            'role' => 'customer',
        ]);

        $matchingDestination = Destination::query()->create([
            'name' => 'Crystal Bay',
            'slug' => 'crystal-bay-filter',
            'description' => 'Spot filter utama.',
            'difficulty' => 'mudah',
            'status' => 'aktif',
        ]);

        $otherDestination = Destination::query()->create([
            'name' => 'Wall Point',
            'slug' => 'wall-point-filter',
            'description' => 'Spot pembanding.',
            'difficulty' => 'menengah',
            'status' => 'aktif',
        ]);

        $matchingPackage = SnorkelingPackage::query()->create([
            'name' => 'Lembongan Morning Escape',
            'slug' => 'lembongan-morning-escape-filter-review',
            'short_description' => 'Trip pagi.',
            'description' => 'Trip pagi untuk review filter.',
            'price' => 450000,
            'duration' => '4 jam',
            'capacity' => 8,
            'status' => 'aktif',
        ]);

        $otherPackage = SnorkelingPackage::query()->create([
            'name' => 'Adventure Reef Run',
            'slug' => 'adventure-reef-run-filter-review',
            'short_description' => 'Trip siang.',
            'description' => 'Trip siang untuk review filter.',
            'price' => 520000,
            'duration' => '4 jam',
            'capacity' => 8,
            'status' => 'aktif',
        ]);

        $matchingPackage->destinations()->sync([$matchingDestination->id]);
        $otherPackage->destinations()->sync([$otherDestination->id]);

        $olderReservation = Reservation::query()->create([
            'code' => 'RVW-FILTER-001',
            'user_id' => $matchingCustomer->id,
            'snorkeling_package_id' => $matchingPackage->id,
            'destination_id' => $matchingDestination->id,
            'booking_date' => now()->subDays(4)->toDateString(),
            'participants' => 2,
            'contact_name' => 'Santa',
            'contact_phone' => '08123456789',
            'pickup_location' => 'Pelabuhan',
            'total_price' => 450000,
            'status' => 'selesai',
        ]);

        $olderReview = Review::query()->create([
            'reservation_id' => $olderReservation->id,
            'user_id' => $matchingCustomer->id,
            'snorkeling_package_id' => $matchingPackage->id,
            'rating' => 5,
            'comment' => 'Review lama yang harus tampil.',
            'status' => 'published',
        ]);
        $olderReview->timestamps = false;
        $olderReview->created_at = now()->subDays(4)->setTime(8, 0);
        $olderReview->updated_at = now()->subDays(4)->setTime(8, 0);
        $olderReview->save();

        $newerReservation = Reservation::query()->create([
            'code' => 'RVW-FILTER-002',
            'user_id' => $matchingCustomer->id,
            'snorkeling_package_id' => $matchingPackage->id,
            'destination_id' => $matchingDestination->id,
            'booking_date' => now()->subDay()->toDateString(),
            'participants' => 2,
            'contact_name' => 'Santa',
            'contact_phone' => '08123456789',
            'pickup_location' => 'Pelabuhan',
            'total_price' => 450000,
            'status' => 'selesai',
        ]);

        $newerReview = Review::query()->create([
            'reservation_id' => $newerReservation->id,
            'user_id' => $matchingCustomer->id,
            'snorkeling_package_id' => $matchingPackage->id,
            'rating' => 5,
            'comment' => 'Review baru yang harus tampil lebih dulu.',
            'status' => 'published',
        ]);
        $newerReview->timestamps = false;
        $newerReview->created_at = now()->subDay()->setTime(10, 0);
        $newerReview->updated_at = now()->subDay()->setTime(10, 0);
        $newerReview->save();

        $otherCustomer = User::factory()->create([
            'name' => 'Xander',
            'role' => 'customer',
        ]);

        $otherReservation = Reservation::query()->create([
            'code' => 'RVW-FILTER-999',
            'user_id' => $otherCustomer->id,
            'snorkeling_package_id' => $otherPackage->id,
            'destination_id' => $otherDestination->id,
            'booking_date' => now()->toDateString(),
            'participants' => 2,
            'contact_name' => 'Xander',
            'contact_phone' => '08123450000',
            'pickup_location' => 'Dermaga',
            'total_price' => 520000,
            'status' => 'selesai',
        ]);

        Review::query()->create([
            'reservation_id' => $otherReservation->id,
            'user_id' => $otherCustomer->id,
            'snorkeling_package_id' => $otherPackage->id,
            'rating' => 3,
            'comment' => 'Review pembanding yang tidak boleh lolos filter.',
            'status' => 'published',
        ]);

        $filteredResponse = $this->get(route('reviews.index', [
            'package' => $matchingPackage->id,
            'destination' => $matchingDestination->id,
            'rating' => '5',
            'order' => 'latest',
        ]));

        $filteredResponse->assertOk();
        $filteredResponse->assertSee('Review baru yang harus tampil lebih dulu.');
        $filteredResponse->assertSee('Review lama yang harus tampil.');
        $filteredResponse->assertDontSee('Review pembanding yang tidak boleh lolos filter.');
        $filteredResponse->assertSeeTextInOrder([
            'Review baru yang harus tampil lebih dulu.',
            'Review lama yang harus tampil.',
        ]);

        $oldestResponse = $this->get(route('reviews.index', [
            'package' => $matchingPackage->id,
            'destination' => $matchingDestination->id,
            'rating' => '5',
            'order' => 'oldest',
        ]));

        $oldestResponse->assertOk();
        $oldestResponse->assertSeeTextInOrder([
            'Review lama yang harus tampil.',
            'Review baru yang harus tampil lebih dulu.',
        ]);
    }
}
