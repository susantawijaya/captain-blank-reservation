<?php

namespace Tests\Feature;

use App\Models\SnorkelingPackage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PackageFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_package_page_can_filter_by_price_range(): void
    {
        SnorkelingPackage::query()->create([
            'name' => 'Paket Hemat',
            'slug' => 'paket-hemat',
            'short_description' => 'Murah',
            'description' => 'Paket murah',
            'price' => 250000,
            'duration' => '4 jam',
            'capacity' => 6,
            'status' => 'aktif',
        ]);

        SnorkelingPackage::query()->create([
            'name' => 'Paket Premium',
            'slug' => 'paket-premium',
            'short_description' => 'Mahal',
            'description' => 'Paket premium',
            'price' => 750000,
            'duration' => '6 jam',
            'capacity' => 8,
            'status' => 'aktif',
        ]);

        $response = $this->get(route('packages.index', ['price_range' => 'lt500000']));

        $response->assertOk();
        $response->assertSee('Paket Hemat');
        $response->assertDontSee('Paket Premium');
    }

    public function test_package_page_can_filter_by_search_keyword(): void
    {
        SnorkelingPackage::query()->create([
            'name' => 'Snorkeling Sunrise',
            'slug' => 'snorkeling-sunrise',
            'short_description' => 'Trip pagi',
            'description' => 'Trip matahari terbit',
            'price' => 300000,
            'duration' => '5 jam',
            'capacity' => 6,
            'status' => 'aktif',
        ]);

        SnorkelingPackage::query()->create([
            'name' => 'Island Hopping',
            'slug' => 'island-hopping',
            'short_description' => 'Trip siang',
            'description' => 'Trip beberapa pulau',
            'price' => 500000,
            'duration' => '7 jam',
            'capacity' => 10,
            'status' => 'aktif',
        ]);

        $response = $this->get(route('packages.index', ['q' => 'sunrise']));

        $response->assertOk();
        $response->assertSee('Snorkeling Sunrise');
        $response->assertDontSee('Island Hopping');
    }

    public function test_nonactive_package_is_hidden_from_public_catalog_and_detail(): void
    {
        SnorkelingPackage::query()->create([
            'name' => 'Paket Aktif',
            'slug' => 'paket-aktif',
            'short_description' => 'Tampil',
            'description' => 'Paket aktif',
            'price' => 250000,
            'duration' => '4 jam',
            'capacity' => 6,
            'status' => 'aktif',
        ]);

        $inactivePackage = SnorkelingPackage::query()->create([
            'name' => 'Paket Nonaktif',
            'slug' => 'paket-nonaktif',
            'short_description' => 'Tidak tampil',
            'description' => 'Paket nonaktif',
            'price' => 350000,
            'duration' => '5 jam',
            'capacity' => 8,
            'status' => 'nonaktif',
        ]);

        $indexResponse = $this->get(route('packages.index'));

        $indexResponse->assertOk();
        $indexResponse->assertSee('Paket Aktif');
        $indexResponse->assertDontSee('Paket Nonaktif');

        $this->get(route('packages.show', $inactivePackage))
            ->assertNotFound();
    }
}
