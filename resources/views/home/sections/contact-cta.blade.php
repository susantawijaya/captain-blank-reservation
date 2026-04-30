<section class="section alt">
    <div class="container">
        <div class="card">
            <div class="card-body" style="display: flex; justify-content: space-between; gap: 24px; align-items: center; flex-wrap: wrap;">
                <div class="max-w-2xl">
                    <span class="text-sm font-black uppercase tracking-[0.16em] text-sky-700">Butuh Bantuan</span>
                    <h2 class="mt-3 text-3xl font-black text-slate-950">Butuh Bantuan Sebelum Reservasi?</h2>
                    <p class="mt-3 leading-7 text-slate-600">Anda bisa langsung membuka halaman kontak untuk mengirim pesan ke admin, atau lanjut ke reservasi jika pilihan paket dan destinasi sudah cocok.</p>
                </div>
                <div class="hero-actions" style="margin: 0;">
                    <a class="button primary" href="{{ route('reservations.create') }}">Mulai Reservasi</a>
                    <a class="button secondary" href="{{ route('contact.index') }}">Hubungi Kami</a>
                </div>
            </div>
        </div>
    </div>
</section>
