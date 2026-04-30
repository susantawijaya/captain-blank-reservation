<section
    class="hero relative overflow-hidden text-white"
    style="
        background-image:
            linear-gradient(120deg, rgba(2, 6, 23, 0.84), rgba(8, 47, 73, 0.68), rgba(15, 118, 110, 0.26)),
            url('{{ asset('images/site/nusa-lembongan-hero.jpg') }}');
        background-position: center;
        background-size: cover;
    "
>
    <div class="container">
        <div class="hero-content">
            <span class="eyebrow">Beranda</span>
            <h1>{{ $company?->name ?? 'Captain Blank' }} Snorkeling</h1>
            <p>
                {{ $company?->name ?? 'Captain Blank' }} membantu pelanggan memilih trip snorkeling dengan alur yang sederhana:
                cek ketersediaan, pilih destinasi, tentukan paket, lalu lanjutkan reservasi tanpa bingung.
            </p>
            <div class="hero-actions">
                <a class="button primary" href="{{ route('reservations.create') }}">Pesan Sekarang</a>
                <a class="button secondary" href="{{ route('packages.index') }}">Lihat Paket</a>
                <a class="button secondary" href="{{ route('contact.index') }}">Hubungi Kami</a>
            </div>
        </div>
    </div>
</section>
