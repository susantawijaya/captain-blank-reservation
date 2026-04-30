<section class="section alt">
    <div class="container">
        <div class="section-head">
            <div>
                <span class="text-sm font-black uppercase tracking-[0.16em] text-sky-700">Destinasi</span>
                <h2>Destinasi Unggulan</h2>
                <p>Beranda hanya menampilkan pilihan utama. Untuk melihat semua spot snorkeling yang tersedia, buka halaman destinasi.</p>
            </div>
            <a class="button secondary" href="{{ route('destinations.index') }}">Semua Destinasi</a>
        </div>
        <div class="grid four">
            @foreach($destinations as $destination)
                @include('destinations.partials.destination-card', ['destination' => $destination])
            @endforeach
        </div>
    </div>
</section>
