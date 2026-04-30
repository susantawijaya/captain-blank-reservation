<section class="section">
    <div class="container">
        <div class="section-head">
            <div>
                <span class="text-sm font-black uppercase tracking-[0.16em] text-sky-700">Paket</span>
                <h2>Paket Unggulan</h2>
                <p>Beranda hanya menampilkan ringkasan paket utama. Semua pilihan paket tetap tersedia lengkap di halaman paket.</p>
            </div>
            <a class="button secondary" href="{{ route('packages.index') }}">Semua Paket</a>
        </div>
        <div class="grid four">
            @foreach($packages as $package)
                @include('packages.partials.package-card', ['package' => $package])
            @endforeach
        </div>
    </div>
</section>
