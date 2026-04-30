@extends('layouts.admin')

@section('title', $reservation->code)

@section('content')
<div class="grid gap-6 xl:grid-cols-2">
    <div class="card">
        <div class="card-body">
            <h1>{{ $reservation->code }}</h1>
            <div class="mt-5 overflow-hidden rounded-2xl border border-slate-200">
                <table class="w-full text-sm">
                    <tbody class="divide-y divide-slate-200">
                        <tr>
                            <th class="w-48 bg-slate-50 px-4 py-3 text-left font-bold text-slate-700">Pelanggan</th>
                            <td class="px-4 py-3 text-slate-900">{{ $reservation->user->name }}</td>
                        </tr>
                        <tr>
                            <th class="bg-slate-50 px-4 py-3 text-left font-bold text-slate-700">Paket</th>
                            <td class="px-4 py-3 text-slate-900">{{ $reservation->package->name }}</td>
                        </tr>
                        <tr>
                            <th class="bg-slate-50 px-4 py-3 text-left font-bold text-slate-700">Destinasi</th>
                            <td class="px-4 py-3 text-slate-900">{{ $reservation->destination?->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th class="bg-slate-50 px-4 py-3 text-left font-bold text-slate-700">Tanggal Trip</th>
                            <td class="px-4 py-3 text-slate-900">{{ optional($reservation->booking_date)->format('d M Y') }}</td>
                        </tr>
                        <tr>
                            <th class="bg-slate-50 px-4 py-3 text-left font-bold text-slate-700">Jam Trip</th>
                            <td class="px-4 py-3 text-slate-900">
                                @if($reservation->schedule)
                                    {{ $reservation->schedule->start_at->format('H:i') }} - {{ $reservation->schedule->end_at->format('H:i') }}
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th class="bg-slate-50 px-4 py-3 text-left font-bold text-slate-700">Peserta</th>
                            <td class="px-4 py-3 text-slate-900">{{ $reservation->adult_count }} dewasa, {{ $reservation->child_count }} anak</td>
                        </tr>
                        <tr>
                            <th class="bg-slate-50 px-4 py-3 text-left font-bold text-slate-700">Harga Charter</th>
                            <td class="px-4 py-3 text-slate-900">Rp {{ number_format($reservation->total_price, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <th class="bg-slate-50 px-4 py-3 text-left font-bold text-slate-700">Status Saat Ini</th>
                            <td class="px-4 py-3">
                                <x-status-badge :status="$reservation->status" :label="$reservation->displayStatusLabel()" />
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            @include('admin.reservations.partials.status-form')
        </div>
    </div>
</div>
@endsection
