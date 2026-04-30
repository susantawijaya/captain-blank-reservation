<div class="card" style="margin-bottom: 16px;">
    <div class="card-body">
        <form class="form-grid" method="GET" action="{{ route('admin.reservations.index') }}" data-auto-filter-form data-auto-submit-delay="200">
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div class="field">
                    <label for="code">Cari kode</label>
                    <input id="code" name="code" type="text" value="{{ $filters['code'] ?? '' }}" placeholder="CBR-..." data-auto-filter-input>
                </div>
                <div class="field">
                    <label for="customer">Cari pelanggan</label>
                    <input id="customer" name="customer" type="text" value="{{ $filters['customer'] ?? '' }}" placeholder="Contoh: Santa" data-auto-filter-input>
                </div>
                <div class="field">
                    <label for="package">Cari paket</label>
                    <input id="package" name="package" type="text" value="{{ $filters['package'] ?? '' }}" placeholder="Contoh: Morning Escape" data-auto-filter-input>
                </div>
                <div class="field">
                    <label for="destination">Cari destinasi</label>
                    <input id="destination" name="destination" type="text" value="{{ $filters['destination'] ?? '' }}" placeholder="Contoh: Crystal Bay" data-auto-filter-input>
                </div>
            </div>
        </form>
    </div>
</div>
