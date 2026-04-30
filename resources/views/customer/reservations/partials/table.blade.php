<div class="table-wrap" data-stack-table>
    <table>
        <thead><tr><th>Kode</th><th>Paket</th><th>Destinasi</th><th>Tanggal</th><th>Status</th><th>Aksi</th></tr></thead>
        <tbody>
            @forelse($reservations as $reservation)
                <tr>
                    <td data-label="Kode">{{ $reservation->code }}</td>
                    <td data-label="Paket">{{ $reservation->package->name }}</td>
                    <td data-label="Destinasi">{{ $reservation->destination?->name ?? '-' }}</td>
                    <td data-label="Tanggal">{{ $reservation->booking_date->format('d M Y') }}</td>
                    <td data-label="Status"><x-status-badge :status="$reservation->status" :label="$reservation->displayStatusLabel()" /></td>
                    <td data-label="Aksi">
                        <div class="flex flex-wrap gap-3">
                            <a href="{{ route('customer.reservations.show', $reservation) }}">Detail</a>
                            @if ($reservation->isCustomerEditable())
                                <a href="{{ route('customer.reservations.edit', $reservation) }}">Edit</a>
                                <form method="POST" action="{{ route('customer.reservations.destroy', $reservation) }}" onsubmit="return confirm('Hapus reservasi {{ $reservation->code }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-sm font-bold text-red-600" type="submit">Hapus</button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6">Belum ada reservasi.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
