<h2>Form Verifikasi</h2>
<form class="form-grid mt-4" method="POST" action="{{ route('admin.payments.update', $payment) }}">
    @csrf
    @method('PUT')
    <div class="field">
        <label for="status">Status Pembayaran</label>
        <select id="status" name="status" required>
            <option value="menunggu_verifikasi" @selected(old('status', $payment->status) === 'menunggu_verifikasi')>Menunggu Konfirmasi</option>
            <option value="diterima" @selected(old('status', $payment->status) === 'diterima')>Diterima</option>
            <option value="ditolak" @selected(old('status', $payment->status) === 'ditolak')>Ditolak</option>
        </select>
        @error('status')
            <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
        @enderror
    </div>
    <div class="field">
        <label for="notes">Catatan</label>
        <textarea id="notes" name="notes" placeholder="Catatan verifikasi">{{ old('notes', $payment->notes) }}</textarea>
        @error('notes')
            <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
        @enderror
    </div>
    <button class="button primary" type="submit">Simpan Verifikasi</button>
</form>
