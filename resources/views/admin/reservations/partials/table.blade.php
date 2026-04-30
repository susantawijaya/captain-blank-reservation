<div class="table-wrap">
    <table>
        <thead><tr><th>Kode</th><th>Pelanggan</th><th>Paket</th><th>Destinasi</th><th>Status</th><th>Total</th><th>Aksi</th></tr></thead>
        <tbody>
        @foreach($reservations as $reservation)
            <tr>
                <td>{{ $reservation->code }}</td>
                <td>{{ $reservation->user->name }}</td>
                <td>{{ $reservation->package->name }}</td>
                <td>{{ $reservation->destination?->name ?? '-' }}</td>
                <td><x-status-badge :status="$reservation->status" :label="$reservation->displayStatusLabel()" /></td>
                <td>Rp {{ number_format($reservation->total_price, 0, ',', '.') }}</td>
                <td><a href="{{ route('admin.reservations.show', $reservation) }}">Detail</a></td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
