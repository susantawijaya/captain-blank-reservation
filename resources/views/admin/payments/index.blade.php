@extends('layouts.admin')

@section('title', 'Verifikasi Pembayaran')

@section('content')
<div class="table-wrap"><table><thead><tr><th>Reservasi</th><th>Pelanggan</th><th>Nominal</th><th>Status</th><th>Aksi</th></tr></thead><tbody>@foreach($payments as $payment)<tr><td>{{ $payment->reservation->code }}</td><td>{{ $payment->reservation->user->name }}</td><td>Rp {{ number_format($payment->amount, 0, ',', '.') }}</td><td><x-status-badge :status="$payment->status" /></td><td><a href="{{ route('admin.payments.show', $payment) }}">Verifikasi</a></td></tr>@endforeach</tbody></table></div>
@endsection
