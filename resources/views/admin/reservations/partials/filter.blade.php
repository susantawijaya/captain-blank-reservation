<div class="card" style="margin-bottom: 16px;">
    <div class="card-body">
        <form class="form-grid" method="GET" action="{{ route('admin.reservations.index') }}" data-auto-filter-form data-auto-submit-delay="300">
            <div class="form-grid two">
                <div class="field">
                    <label for="q">Cari kode</label>
                    <input id="q" name="q" type="text" value="{{ $filters['q'] ?? '' }}" placeholder="CBR-..." data-auto-filter-input>
                </div>
                <div class="field">
                    <label for="status">Status</label>
                    <select id="status" name="status" data-auto-filter-change>
                        <option value="all" @selected(($filters['status'] ?? 'all') === 'all')>Semua</option>
                        <option value="menunggu_pembayaran" @selected(($filters['status'] ?? 'all') === 'menunggu_pembayaran')>Menunggu Pembayaran</option>
                        <option value="menunggu_verifikasi" @selected(($filters['status'] ?? 'all') === 'menunggu_verifikasi')>Menunggu Konfirmasi</option>
                        <option value="terkonfirmasi" @selected(($filters['status'] ?? 'all') === 'terkonfirmasi')>Terkonfirmasi</option>
                        <option value="selesai" @selected(($filters['status'] ?? 'all') === 'selesai')>Selesai</option>
                        <option value="dibatalkan" @selected(($filters['status'] ?? 'all') === 'dibatalkan')>Dibatalkan</option>
                        <option value="dijadwalkan_ulang" @selected(($filters['status'] ?? 'all') === 'dijadwalkan_ulang')>Dijadwalkan Ulang</option>
                    </select>
                </div>
            </div>
        </form>
    </div>
</div>
