<?php

namespace Database\Seeders;

use App\Models\CompanyProfile;
use App\Models\Destination;
use App\Models\Faq;
use App\Models\GalleryItem;
use App\Models\Schedule;
use App\Models\SnorkelingPackage;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;

class WebsiteContentSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedCompanyProfile();

        $destinations = collect($this->destinationPayloads())
            ->mapWithKeys(function (array $payload) {
                $destination = Destination::query()->updateOrCreate(
                    ['slug' => $payload['slug']],
                    $payload,
                );

                return [$payload['slug'] => $destination];
            });

        $packages = collect($this->packagePayloads())
            ->mapWithKeys(function (array $payload) use ($destinations) {
                $package = SnorkelingPackage::query()->updateOrCreate(
                    ['slug' => $payload['slug']],
                    collect($payload)->except('destination_slugs')->all(),
                );

                $package->destinations()->syncWithoutDetaching(
                    collect($payload['destination_slugs'])
                        ->map(fn (string $slug) => $destinations[$slug]->id)
                        ->all(),
                );

                return [$payload['slug'] => $package];
            });

        $this->seedSchedules($packages);
        $this->seedGallery();
        $this->seedFaqs();
    }

    private function seedCompanyProfile(): void
    {
        $profile = CompanyProfile::query()->first() ?? new CompanyProfile();

        $profile->fill([
            'name' => 'Captain Blank',
            'tagline' => 'Private boat snorkeling di Nusa Lembongan dengan pilihan trip yang rapi, jelas, dan siap dipesan online.',
            'description' => 'Captain Blank adalah layanan reservasi private boat snorkeling yang berfokus pada kemudahan memilih destinasi, melihat ketersediaan kapal, dan mengatur perjalanan laut secara lebih praktis. Website ini membantu admin mengelola paket, jadwal, dan armada, sambil memberi pelanggan alur reservasi yang lebih sederhana.',
            'phone' => '0366-554433',
            'whatsapp' => '081998887776',
            'email' => 'halo@captainblank.com',
            'address' => 'Jungut Batu, Nusa Lembongan, Klungkung, Bali',
            'bank_name' => 'Mandiri',
            'bank_account_number' => '987654321001',
            'bank_account_name' => 'Captain Blank Tour',
            'instagram' => '@captainblank.snorkeling',
            'logo_path' => 'images/brand/logo-captain-blank.svg',
        ]);

        $profile->save();
    }

    private function destinationPayloads(): array
    {
        return [
            [
                'name' => 'Manta Bay',
                'slug' => 'manta-bay',
                'description' => 'Spot ikonik dengan panorama tebing dan jalur air biru gelap yang sering dipilih untuk pengalaman private trip yang lebih berkesan.',
                'image_path' => 'images/site/nusa-lembongan-hero.jpg',
                'difficulty' => 'menengah',
                'status' => 'aktif',
            ],
            [
                'name' => 'Crystal Bay',
                'slug' => 'crystal-bay',
                'description' => 'Air jernih dan warna laut yang terang membuat spot ini cocok untuk tamu yang ingin snorkeling santai dengan visual bawah laut yang jelas.',
                'image_path' => 'images/site/lembongan-package-detail.jpg',
                'difficulty' => 'mudah',
                'status' => 'aktif',
            ],
            [
                'name' => 'Gamat Bay',
                'slug' => 'gamat-bay',
                'description' => 'Area snorkeling dengan kombinasi karang, arus ringan, dan warna air yang khas. Cocok untuk tamu yang ingin eksplorasi spot populer di Lembongan.',
                'image_path' => 'images/site/lembongan-packages.jpg',
                'difficulty' => 'menengah',
                'status' => 'aktif',
            ],
            [
                'name' => 'Mangrove Point',
                'slug' => 'mangrove-point',
                'description' => 'Perairan relatif tenang dengan nuansa terang di siang hari, sering dipilih untuk rombongan keluarga dan tamu pemula.',
                'image_path' => 'images/site/nusa-lembongan-hero.jpg',
                'difficulty' => 'mudah',
                'status' => 'aktif',
            ],
            [
                'name' => 'Wall Point',
                'slug' => 'wall-point',
                'description' => 'Spot dengan kontur bawah laut yang lebih dramatis, cocok untuk tamu yang ingin pengalaman snorkeling yang terasa lebih eksploratif.',
                'image_path' => 'images/site/lembongan-package-detail.jpg',
                'difficulty' => 'lanjutan',
                'status' => 'aktif',
            ],
            [
                'name' => 'Turtle Corner',
                'slug' => 'turtle-corner',
                'description' => 'Area favorit untuk private trip santai yang ingin mengejar momen melihat penyu di perairan dangkal dan jernih.',
                'image_path' => 'images/site/lembongan-packages.jpg',
                'difficulty' => 'mudah',
                'status' => 'aktif',
            ],
        ];
    }

    private function packagePayloads(): array
    {
        return [
            [
                'name' => 'Lembongan Morning Escape',
                'slug' => 'lembongan-morning-escape',
                'short_description' => 'Trip pagi dengan alur ringan untuk tamu yang ingin private boat singkat namun tetap berisi.',
                'description' => 'Paket ini dirancang untuk tamu yang ingin berangkat pagi, mengunjungi spot pilihan, lalu kembali lebih cepat dengan ritme trip yang tetap santai dan efisien.',
                'price' => 450000,
                'duration' => '4 jam',
                'capacity' => 8,
                'facilities' => 'Private boat, alat snorkeling, life jacket, air mineral, dry bag bersama, dan crew pendamping.',
                'image_path' => null,
                'status' => 'aktif',
                'destination_slugs' => ['crystal-bay', 'mangrove-point', 'turtle-corner'],
            ],
            [
                'name' => 'West Coast Explorer',
                'slug' => 'west-coast-explorer',
                'short_description' => 'Private boat trip lebih panjang untuk tamu yang ingin mengunjungi beberapa spot populer sekaligus.',
                'description' => 'Paket ini cocok untuk rombongan yang ingin menggabungkan beberapa spot snorkeling utama dalam satu hari dengan waktu jelajah yang lebih panjang.',
                'price' => 900000,
                'duration' => '6 jam',
                'capacity' => 12,
                'facilities' => 'Private boat, alat snorkeling lengkap, life jacket, es box air mineral, dokumentasi dasar, dan crew lokal.',
                'image_path' => null,
                'status' => 'aktif',
                'destination_slugs' => ['manta-bay', 'gamat-bay', 'crystal-bay'],
            ],
            [
                'name' => 'Family Leisure Charter',
                'slug' => 'family-leisure-charter',
                'short_description' => 'Trip santai untuk keluarga kecil dengan fokus kenyamanan, ritme pelan, dan spot yang ramah pemula.',
                'description' => 'Private charter ini dibuat untuk keluarga yang ingin perjalanan lebih santai, tidak terburu-buru, dan tetap punya pilihan spot yang aman untuk anak maupun orang tua.',
                'price' => 780000,
                'duration' => '5 jam',
                'capacity' => 6,
                'facilities' => 'Private boat, alat snorkeling, pelampung anak dan dewasa, snack ringan, air mineral, dan bantuan crew.',
                'image_path' => null,
                'status' => 'aktif',
                'destination_slugs' => ['mangrove-point', 'turtle-corner', 'crystal-bay'],
            ],
            [
                'name' => 'Adventure Reef Run',
                'slug' => 'adventure-reef-run',
                'short_description' => 'Trip untuk tamu yang ingin lintasan spot lebih aktif dengan nuansa eksplorasi yang terasa.',
                'description' => 'Paket ini menyasar tamu yang ingin private boat dengan rute lebih dinamis, memadukan spot terang dan spot dengan karakter bawah laut yang lebih menantang.',
                'price' => 980000,
                'duration' => '5 jam',
                'capacity' => 10,
                'facilities' => 'Private boat, alat snorkeling, life jacket, pemandu trip, air mineral, dan briefing keselamatan.',
                'image_path' => null,
                'status' => 'aktif',
                'destination_slugs' => ['gamat-bay', 'wall-point', 'manta-bay'],
            ],
            [
                'name' => 'Sunset Turtle Route',
                'slug' => 'sunset-turtle-route',
                'short_description' => 'Trip sore untuk tamu yang ingin perjalanan lebih singkat dengan penutupan suasana laut yang hangat.',
                'description' => 'Paket sore ini cocok untuk tamu yang baru punya waktu di siang akhir, namun tetap ingin merasakan private boat snorkeling dan spot penyu dalam durasi yang ringkas.',
                'price' => 520000,
                'duration' => '3.5 jam',
                'capacity' => 7,
                'facilities' => 'Private boat, alat snorkeling, life jacket, air mineral, dan crew pendamping.',
                'image_path' => null,
                'status' => 'aktif',
                'destination_slugs' => ['turtle-corner', 'mangrove-point'],
            ],
        ];
    }

    private function seedSchedules($packages): void
    {
        $baseDate = CarbonImmutable::now()->startOfDay()->addDays(1);

        $scheduleBlueprints = [
            ['slug' => 'lembongan-morning-escape', 'day' => 0, 'hour' => 8, 'duration_hours' => 4, 'capacity' => 8, 'boat_count' => 4, 'booked_count' => 0, 'status' => 'tersedia', 'weather_note' => 'Cuaca pagi cenderung cerah dengan pergerakan laut ringan.'],
            ['slug' => 'lembongan-morning-escape', 'day' => 2, 'hour' => 8, 'duration_hours' => 4, 'capacity' => 8, 'boat_count' => 4, 'booked_count' => 0, 'status' => 'tersedia', 'weather_note' => 'Jadwal pagi dibuka penuh untuk tamu yang ingin trip cepat dan rapi.'],
            ['slug' => 'west-coast-explorer', 'day' => 1, 'hour' => 9, 'duration_hours' => 6, 'capacity' => 12, 'boat_count' => 3, 'booked_count' => 0, 'status' => 'tersedia', 'weather_note' => 'Cocok untuk tamu yang ingin lintasan spot lebih panjang.'],
            ['slug' => 'west-coast-explorer', 'day' => 4, 'hour' => 9, 'duration_hours' => 6, 'capacity' => 12, 'boat_count' => 3, 'booked_count' => 0, 'status' => 'tersedia', 'weather_note' => 'Semua kapal pada slot ini masih kosong dan siap dipesan.'],
            ['slug' => 'family-leisure-charter', 'day' => 3, 'hour' => 8, 'duration_hours' => 5, 'capacity' => 6, 'boat_count' => 2, 'booked_count' => 0, 'status' => 'tersedia', 'weather_note' => 'Ritme trip dibuat santai untuk keluarga dan tamu pemula.'],
            ['slug' => 'family-leisure-charter', 'day' => 6, 'hour' => 8, 'duration_hours' => 5, 'capacity' => 6, 'boat_count' => 2, 'booked_count' => 0, 'status' => 'tersedia', 'weather_note' => 'Slot keluarga masih benar-benar kosong tanpa reservasi masuk.'],
            ['slug' => 'adventure-reef-run', 'day' => 1, 'hour' => 7, 'duration_hours' => 5, 'capacity' => 10, 'boat_count' => 3, 'booked_count' => 0, 'status' => 'tersedia', 'weather_note' => 'Waktu berangkat dibuat lebih awal agar rute eksplorasi terasa lebih leluasa.'],
            ['slug' => 'adventure-reef-run', 'day' => 5, 'hour' => 7, 'duration_hours' => 5, 'capacity' => 10, 'boat_count' => 3, 'booked_count' => 0, 'status' => 'tersedia', 'weather_note' => 'Semua kapal adventure run pada jadwal ini masih tersedia penuh.'],
            ['slug' => 'sunset-turtle-route', 'day' => 2, 'hour' => 14, 'duration_hours' => 4, 'capacity' => 7, 'boat_count' => 2, 'booked_count' => 0, 'status' => 'tersedia', 'weather_note' => 'Trip sore cocok untuk tamu yang ingin suasana lebih hangat menjelang matahari turun.'],
            ['slug' => 'sunset-turtle-route', 'day' => 7, 'hour' => 14, 'duration_hours' => 4, 'capacity' => 7, 'boat_count' => 2, 'booked_count' => 0, 'status' => 'tersedia', 'weather_note' => 'Belum ada reservasi pada slot sore ini, semua kapal siap dipakai.'],
        ];

        foreach ($scheduleBlueprints as $blueprint) {
            $package = $packages[$blueprint['slug']];
            $startAt = $baseDate->addDays($blueprint['day'])->setTime($blueprint['hour'], 0);
            $endAt = $startAt->addHours($blueprint['duration_hours']);

            Schedule::query()->updateOrCreate(
                [
                    'snorkeling_package_id' => $package->id,
                    'start_at' => $startAt,
                ],
                [
                    'end_at' => $endAt,
                    'capacity' => $blueprint['capacity'],
                    'boat_count' => $blueprint['boat_count'],
                    'booked_count' => $blueprint['booked_count'],
                    'status' => $blueprint['status'],
                    'weather_note' => $blueprint['weather_note'],
                    'destination_note' => 'Rute dapat disesuaikan oleh admin jika cuaca atau arus laut berubah.',
                ],
            );
        }
    }

    private function seedGallery(): void
    {
        $items = [
            ['title' => 'Briefing Private Boat Pagi', 'category' => 'operasional', 'caption' => 'Kru menyiapkan trip pagi sebelum tamu naik ke kapal private.', 'is_featured' => true],
            ['title' => 'Crystal Water Session', 'category' => 'snorkeling', 'caption' => 'Area air terang untuk tamu yang ingin suasana snorkeling yang bersih dan santai.', 'is_featured' => true],
            ['title' => 'Manta Bay Crossing', 'category' => 'laut', 'caption' => 'Jalur trip menuju spot ikonik yang memberi nuansa eksplorasi lebih kuat.', 'is_featured' => true],
            ['title' => 'Family Leisure On Board', 'category' => 'keluarga', 'caption' => 'Suasana private charter keluarga dengan ritme perjalanan yang lebih tenang.', 'is_featured' => true],
            ['title' => 'Mangrove Calm Route', 'category' => 'pulau', 'caption' => 'Spot yang cocok untuk tamu pemula dan keluarga kecil.', 'is_featured' => true],
            ['title' => 'Sunset Turtle Route Deck', 'category' => 'sunset', 'caption' => 'Trip sore dengan suasana kapal yang hangat dan santai menjelang petang.', 'is_featured' => true],
            ['title' => 'Adventure Reef Equipment Check', 'category' => 'peralatan', 'caption' => 'Pengecekan alat untuk rute yang lebih aktif dan eksploratif.', 'is_featured' => false],
            ['title' => 'Open Charter Dock', 'category' => 'operasional', 'caption' => 'Area keberangkatan kapal yang masih bersih tanpa antrean tamu.', 'is_featured' => false],
        ];

        foreach ($items as $item) {
            GalleryItem::query()->updateOrCreate(
                ['title' => $item['title']],
                [
                    'image_path' => 'images/site/hero-ocean.svg',
                    'category' => $item['category'],
                    'caption' => $item['caption'],
                    'is_featured' => $item['is_featured'],
                ],
            );
        }
    }

    private function seedFaqs(): void
    {
        $faqs = [
            [
                'question' => 'Apakah pemula bisa ikut trip snorkeling?',
                'answer' => 'Bisa. Beberapa paket dibuat khusus untuk tamu pemula, keluarga, atau rombongan santai, dan kru akan membantu briefing dasar sebelum trip dimulai.',
                'sort_order' => 1,
            ],
            [
                'question' => 'Apakah harga dihitung per orang atau per kapal?',
                'answer' => 'Harga pada website ini mengikuti sistem private charter per kapal. Jumlah peserta tetap perlu diisi, tetapi harga paket tidak dihitung ulang per orang.',
                'sort_order' => 2,
            ],
            [
                'question' => 'Bagaimana sistem pembayarannya?',
                'answer' => 'Pelanggan melakukan transfer ke rekening yang tersedia, lalu mengunggah bukti pembayaran agar admin dapat memverifikasi reservasi.',
                'sort_order' => 3,
            ],
            [
                'question' => 'Apakah jadwal bisa berubah karena cuaca laut?',
                'answer' => 'Bisa. Admin dapat menutup atau menjadwalkan ulang trip jika kondisi ombak, angin, atau arus tidak aman untuk private boat berangkat.',
                'sort_order' => 4,
            ],
            [
                'question' => 'Apakah saya bisa memilih tanggal dan jam trip sendiri?',
                'answer' => 'Pelanggan memilih tanggal terlebih dahulu, lalu sistem akan menampilkan jam trip yang memang tersedia di tanggal itu.',
                'sort_order' => 5,
            ],
            [
                'question' => 'Apa saja yang perlu dibawa saat private trip?',
                'answer' => 'Disarankan membawa pakaian ganti, handuk, sunblock, obat pribadi, dan perlengkapan pribadi yang memang Anda perlukan selama di laut.',
                'sort_order' => 6,
            ],
        ];

        foreach ($faqs as $faq) {
            Faq::query()->updateOrCreate(
                ['question' => $faq['question']],
                $faq,
            );
        }
    }
}
