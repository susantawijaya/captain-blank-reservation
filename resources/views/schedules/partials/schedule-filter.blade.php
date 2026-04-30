<div class="card" style="margin-bottom: 20px;">
    <div class="card-body">
        <form class="form-grid" method="GET" action="{{ route('schedules.index') }}" data-auto-filter-form data-auto-submit-delay="300">
            <div class="form-grid two">
                <div class="field">
                    <label for="q">Cari jadwal atau paket</label>
                    <input id="q" name="q" type="text" value="{{ $filters['q'] ?? '' }}" placeholder="Contoh: Sunrise atau Island Hopping" data-auto-filter-input>
                </div>
                <div class="field">
                    <label for="status">Filter status</label>
                    <select id="status" name="status" data-auto-filter-change>
                        <option value="all" @selected(($filters['status'] ?? 'all') === 'all')>Semua status</option>
                        <option value="tersedia" @selected(($filters['status'] ?? 'all') === 'tersedia')>Tersedia</option>
                        <option value="penuh" @selected(($filters['status'] ?? 'all') === 'penuh')>Penuh</option>
                    </select>
                </div>
            </div>
        </form>
    </div>
</div>
