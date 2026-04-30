<div class="card" style="margin-bottom: 20px;">
    <div class="card-body">
        <form class="form-grid" method="GET" action="{{ route('packages.index') }}" data-auto-filter-form data-auto-submit-delay="200">
            @if(!empty($filters['destination']))
                <input type="hidden" name="destination" value="{{ $filters['destination'] }}">
            @endif
            <div class="grid gap-4 lg:grid-cols-5">
                <div class="field">
                    <label for="q">Cari paket</label>
                    <input id="q" name="q" type="text" value="{{ $filters['q'] ?? '' }}" placeholder="Contoh: Snorkeling Pagi" data-auto-filter-input>
                </div>
                <div class="field">
                    <label for="date">Tanggal Reservasi</label>
                    <input id="date" name="date" type="date" min="{{ now()->toDateString() }}" value="{{ $filters['date'] ?? '' }}" data-auto-filter-change>
                </div>
                <div class="field">
                    <label for="adult_count">Dewasa</label>
                    <input id="adult_count" name="adult_count" type="number" min="1" max="50" value="{{ $filters['adult_count'] ?? 2 }}" data-auto-filter-change>
                </div>
                <div class="field">
                    <label for="child_count">Anak</label>
                    <input id="child_count" name="child_count" type="number" min="0" max="50" value="{{ $filters['child_count'] ?? 0 }}" data-auto-filter-change>
                </div>
                <div class="field">
                    <label for="price_range">Filter harga</label>
                    <select id="price_range" name="price_range" data-auto-filter-change>
                        <option value="all" @selected(($filters['price_range'] ?? 'all') === 'all')>Semua harga</option>
                        <option value="lt500000" @selected(($filters['price_range'] ?? 'all') === 'lt500000')>Di bawah Rp 500.000</option>
                        <option value="gte500000" @selected(($filters['price_range'] ?? 'all') === 'gte500000')>Rp 500.000 ke atas</option>
                    </select>
                </div>
            </div>
        </form>
    </div>
</div>
