<div class="table-wrap">
    <table>
        <thead><tr><th>Kode</th><th>Paket</th><th>Destinasi</th><th>Tanggal</th><th>Status</th><th>Aksi</th></tr></thead>
        <tbody>
            @forelse($reservations as $reservation)
                <tr>
                    <td>{{ $reservation->code }}</td>
                    <td>{{ $reservation->package->name }}</td>
                    <td>{{ $reservation->destination?->name ?? '-' }}</td>
                    <td>{{ $reservation->booking_date->format('d M Y') }}</td>
                    <td><x-status-badge :status="$reservation->status" :label="$reservation->displayStatusLabel()" /></td>
                    <td>
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
