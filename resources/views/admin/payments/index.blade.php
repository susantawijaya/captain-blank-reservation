@extends('layouts.admin')

@section('title', 'Verifikasi Pembayaran')

@section('content')
<div class="card" style="margin-bottom: 16px;">
    <div class="card-body">
        <form class="form-grid" method="GET" action="{{ route('admin.payments.index') }}" data-auto-filter-form data-auto-submit-delay="200">
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
                    <label for="status">Cari status</label>
                    <select id="status" name="status" data-auto-filter-change>
                        <option value="all" @selected(($filters['status'] ?? 'all') === 'all')>Semua status</option>
                        <option value="belum_bayar" @selected(($filters['status'] ?? 'all') === 'belum_bayar')>Belum Bayar</option>
                        <option value="menunggu_verifikasi" @selected(($filters['status'] ?? 'all') === 'menunggu_verifikasi')>Menunggu Konfirmasi</option>
                        <option value="diterima" @selected(($filters['status'] ?? 'all') === 'diterima')>Diterima</option>
                        <option value="ditolak" @selected(($filters['status'] ?? 'all') === 'ditolak')>Ditolak</option>
                    </select>
                </div>
                <div class="field">
                    <label for="date">Cari tanggal</label>
                    <input id="date" name="date" type="date" value="{{ $filters['date'] ?? '' }}" data-auto-filter-change>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>Reservasi</th>
                <th>Pelanggan</th>
                <th>Tanggal Pembayaran</th>
                <th>Nominal</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($payments as $payment)
                <tr>
                    <td>{{ $payment->reservation->code }}</td>
                    <td>{{ $payment->reservation->user->name }}</td>
                    <td>{{ $payment->created_at?->translatedFormat('d M Y H:i') ?? '-' }}</td>
                    <td>Rp {{ number_format($payment->amount, 0, ',', '.') }}</td>
                    <td><x-status-badge :status="$payment->status" /></td>
                    <td><a href="{{ route('admin.payments.show', $payment) }}">Verifikasi</a></td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">Belum ada data pembayaran.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
