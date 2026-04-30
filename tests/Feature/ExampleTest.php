<?php

namespace Tests\Feature;

use App\Models\Destination;
use App\Models\Reservation;
use App\Models\Schedule;
use App\Models\SnorkelingPackage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_old_about_url_redirects_to_homepage_about_section(): void
    {
        $response = $this->get('/tentang');

        $response->assertRedirect(route('home').'#tentang');
    }

    public function test_homepage_uses_most_reserved_packages_and_destinations_as_featured_items(): void
    {
        $customer = User::factory()->create([
            'role' => 'customer',
        ]);

        $packages = collect();
        $destinations = collect();

        foreach (range(1, 5) as $index) {
            $package = SnorkelingPackage::query()->create([
                'name' => 'Paket Unggulan '.$index,
                'slug' => 'paket-unggulan-'.$index,
                'short_description' => 'Deskripsi singkat paket '.$index,
                'description' => 'Deskripsi lengkap paket '.$index,
                'price' => 200000 + ($index * 10000),
                'duration' => '4 jam',
                'capacity' => 10,
                'status' => 'aktif',
            ]);

            $destination = Destination::query()->create([
                'name' => 'Destinasi Unggulan '.$index,
                'slug' => 'destinasi-unggulan-'.$index,
                'description' => 'Deskripsi destinasi '.$index,
                'difficulty' => 'mudah',
                'status' => 'aktif',
            ]);

            $package->destinations()->sync([$destination->id]);

            $schedule = Schedule::query()->create([
                'snorkeling_package_id' => $package->id,
                'start_at' => now()->addDays($index)->setTime(8, 0),
                'end_at' => now()->addDays($index)->setTime(12, 0),
                'capacity' => 10,
                'boat_count' => 5,
                'booked_count' => 0,
                'status' => 'tersedia',
            ]);

            foreach (range(1, 6 - $index) as $reservationIndex) {
                Reservation::query()->create([
                    'code' => sprintf('CBR-FEAT-%d-%d', $index, $reservationIndex),
                    'user_id' => $customer->id,
                    'snorkeling_package_id' => $package->id,
                    'destination_id' => $destination->id,
                    'schedule_id' => $schedule->id,
                    'booking_date' => $schedule->start_at->toDateString(),
                    'participants' => 1,
                    'adult_count' => 1,
                    'child_count' => 0,
                    'contact_name' => 'Customer Test',
                    'contact_phone' => '08123456789',
                    'total_price' => $package->price,
                    'status' => 'menunggu_pembayaran',
                ]);
            }

            $packages->push($package);
            $destinations->push($destination);
        }

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('Paket Unggulan 1');
        $response->assertSee('Paket Unggulan 2');
        $response->assertSee('Paket Unggulan 3');
        $response->assertSee('Paket Unggulan 4');
        $response->assertDontSee('Paket Unggulan 5');
        $response->assertSee('Destinasi Unggulan 1');
        $response->assertSee('Destinasi Unggulan 2');
        $response->assertSee('Destinasi Unggulan 3');
        $response->assertSee('Destinasi Unggulan 4');
        $response->assertDontSee('Destinasi Unggulan 5');
    }
}
