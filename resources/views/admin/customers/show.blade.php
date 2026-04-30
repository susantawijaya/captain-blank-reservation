@extends('layouts.admin')

@section('title', $customer->name)
@section('admin_topbar_actions')
    <a class="button secondary" href="{{ route('admin.customers.edit', $customer) }}">Edit Pelanggan</a>
    <form method="POST" action="{{ route('admin.customers.destroy', $customer) }}" onsubmit="return confirm('Hapus akun pelanggan {{ $customer->name }}?');">
        @csrf
        @method('DELETE')
        <button class="button secondary !border-red-200 !text-red-700 hover:!bg-red-50" type="submit">Hapus</button>
    </form>
@endsection

@section('content')
<div class="grid gap-6 lg:grid-cols-[320px,1fr]">
    <div class="card">
        <div class="card-body">
            <h2>Profil Pelanggan</h2>
            <dl class="mt-5 space-y-4 text-sm text-slate-600">
                <div>
                    <dt class="font-bold text-slate-900">Nama</dt>
                    <dd class="mt-1">{{ $customer->name }}</dd>
                </div>
                <div>
                    <dt class="font-bold text-slate-900">Email</dt>
                    <dd class="mt-1">{{ $customer->email }}</dd>
                </div>
                <div>
                    <dt class="font-bold text-slate-900">WhatsApp</dt>
                    <dd class="mt-1">{{ $customer->phone ?: '-' }}</dd>
                </div>
                <div>
                    <dt class="font-bold text-slate-900">Alamat</dt>
                    <dd class="mt-1">{{ $customer->address ?: '-' }}</dd>
                </div>
                <div>
                    <dt class="font-bold text-slate-900">Jumlah Reservasi</dt>
                    <dd class="mt-1">{{ $customer->reservations->count() }}</dd>
                </div>
                <div>
                    <dt class="font-bold text-slate-900">Jumlah Review</dt>
                    <dd class="mt-1">{{ $customer->reviews->count() }}</dd>
                </div>
                <div>
                    <dt class="font-bold text-slate-900">Jumlah Pesan</dt>
                    <dd class="mt-1">{{ $customer->complaints->count() }}</dd>
                </div>
            </dl>
        </div>
    </div>

    <div class="space-y-6">
        <div class="card">
            <div class="card-body">
                <h2>Riwayat Reservasi</h2>
                <div class="mt-5">
                    @include('admin.reservations.partials.table', ['reservations' => $customer->reservations])
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h2>Review Pelanggan</h2>
                <div class="table-wrap mt-5">
                    <table>
                        <thead>
                            <tr>
                                <th>Paket</th>
                                <th>Rating</th>
                                <th>Status</th>
                                <th>Komentar</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($customer->reviews as $review)
                                <tr>
                                    <td>{{ $review->package?->name ?? '-' }}</td>
                                    <td>{{ $review->rating }}/5</td>
                                    <td>{{ ucfirst($review->status) }}</td>
                                    <td>{{ $review->comment }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4">Belum ada review dari pelanggan ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
