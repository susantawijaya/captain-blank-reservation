<div class="card" style="margin-bottom: 20px;">
    <div class="card-body">
        <form class="form-grid" method="GET" action="{{ route('destinations.index') }}" data-auto-filter-form data-auto-submit-delay="200">
            <div class="form-grid two">
                <div class="field">
                    <label for="q">Cari destinasi</label>
                    <input id="q" name="q" type="text" value="{{ $filters['q'] ?? '' }}" placeholder="Contoh: Gili Nanggu" data-auto-filter-input>
                </div>
                <div class="field">
                    <label for="difficulty">Filter tingkat kesulitan</label>
                    <select id="difficulty" name="difficulty" data-auto-filter-change>
                        <option value="all" @selected(($filters['difficulty'] ?? 'all') === 'all')>Semua tingkat</option>
                        <option value="mudah" @selected(($filters['difficulty'] ?? 'all') === 'mudah')>Mudah</option>
                        <option value="menengah" @selected(($filters['difficulty'] ?? 'all') === 'menengah')>Menengah</option>
                        <option value="lanjutan" @selected(($filters['difficulty'] ?? 'all') === 'lanjutan')>Lanjutan</option>
                    </select>
                </div>
            </div>
        </form>
    </div>
</div>
