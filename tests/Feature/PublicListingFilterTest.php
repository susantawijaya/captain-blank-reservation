<?php

namespace Tests\Feature;

use App\Models\Destination;
use App\Models\SnorkelingPackage;
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
}
