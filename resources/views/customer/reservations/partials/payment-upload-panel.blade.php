<div class="card">
    <div class="card-body">
        <h2>Upload Bukti Pembayaran</h2>
        @if($reservation->payment?->status === 'ditolak')
            <p class="mt-3 text-sm text-slate-600">Pembayaran sebelumnya ditolak admin. Silakan transfer ulang jika diperlukan, lalu unggah bukti pembayaran terbaru di sini.</p>
        @else
            <p class="mt-3 text-sm text-slate-600">Transfer sesuai nominal reservasi, lalu unggah bukti pembayaran di sini agar admin bisa memverifikasi.</p>
        @endif

        @if($company?->bank_name && $company?->bank_account_number)
            <div class="mt-5 rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-700">
                <p><strong>Bank:</strong> {{ $company->bank_name }}</p>
                <p class="mt-1"><strong>No. Rekening:</strong> {{ $company->bank_account_number }}</p>
                <p class="mt-1"><strong>Atas Nama:</strong> {{ $company->bank_account_name }}</p>
            </div>
        @endif

        <form class="form-grid mt-5" method="POST" action="{{ route('customer.reservations.payment.store', $reservation) }}" enctype="multipart/form-data">
            @csrf
            <div class="field">
                <label for="amount">Nominal Transfer</label>
                <input id="amount" type="number" value="{{ $reservation->total_price }}" readonly>
            </div>
            <div class="field">
                <label for="proof_image">Bukti Transfer</label>
                <input
                    id="proof_image"
                    name="proof_image"
                    type="file"
                    accept="image/*"
                    required
                    data-image-preview-input
                    data-image-preview-target="payment-proof-preview"
                    data-image-preview-name="payment-proof-preview-name"
                >
                <div class="upload-preview" id="payment-proof-preview" data-image-preview-wrapper hidden>
                    <p class="upload-preview-label">Preview bukti transfer</p>
                    <img class="upload-preview-image" data-image-preview-image alt="Preview bukti transfer">
                    <p class="upload-preview-name" id="payment-proof-preview-name" data-image-preview-filename></p>
                </div>
                @error('proof_image')
                    <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div class="field">
                <label for="notes">Catatan</label>
                <textarea id="notes" name="notes" placeholder="Catatan tambahan pembayaran">{{ old('notes') }}</textarea>
                @error('notes')
                    <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <button class="button primary" type="submit">Kirim Bukti Pembayaran</button>
        </form>
    </div>
</div>
