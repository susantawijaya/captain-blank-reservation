<h2>Status Reservasi</h2>
<div class="mt-4 space-y-4">
    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
        <p class="text-sm font-semibold text-slate-900">Status saat ini</p>
        <div class="mt-2">
            <x-status-badge :status="$reservation->status" :label="$reservation->displayStatusLabel()" />
        </div>
        @if($reservation->statusContextNote())
            <p class="mt-3 text-sm leading-6 text-slate-600">{{ $reservation->statusContextNote() }}</p>
        @endif
    </div>

    @if($reservation->status === 'terkonfirmasi')
        <form class="form-grid" method="POST" action="{{ route('admin.reservations.update', $reservation) }}">
            @csrf
            @method('PUT')
            <div class="field">
                <label for="notes">Catatan Admin</label>
                <textarea id="notes" name="notes" placeholder="Catatan setelah trip selesai">{{ old('notes', $reservation->notes) }}</textarea>
                @error('notes')
                    <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <button class="button primary" type="submit">Tandai Selesai</button>
        </form>
    @elseif($reservation->status === 'selesai')
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-medium text-emerald-800">
            Reservasi ini sudah selesai.
        </div>
    @endif

    @if($reservation->payment)
        <a class="button secondary" href="{{ route('admin.payments.show', $reservation->payment) }}">Lihat Detail Pembayaran</a>
    @endif
</div>
