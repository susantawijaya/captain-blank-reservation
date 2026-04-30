@extends('layouts.admin')

@section('title', 'Data Pelanggan')
@section('admin_topbar_actions')
    <a class="button primary" href="{{ route('admin.customers.create') }}">Tambah Pelanggan</a>
@endsection

@section('content')
<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>Nama</th>
                <th>Email</th>
                <th>WhatsApp</th>
                <th>Reservasi</th>
                <th>Review</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($customers as $customer)
                <tr>
                    <td><strong>{{ $customer->name }}</strong></td>
                    <td>{{ $customer->email }}</td>
                    <td>{{ $customer->phone ?: '-' }}</td>
                    <td>{{ $customer->reservations_count }}</td>
                    <td>{{ $customer->reviews_count }}</td>
                    <td>
                        <div class="flex flex-wrap gap-3">
                            <a href="{{ route('admin.customers.show', $customer) }}">Detail</a>
                            <a href="{{ route('admin.customers.edit', $customer) }}">Edit</a>
                            <form method="POST" action="{{ route('admin.customers.destroy', $customer) }}" onsubmit="return confirm('Hapus akun pelanggan {{ $customer->name }}?');">
                                @csrf
                                @method('DELETE')
                                <button class="text-sm font-bold text-red-600" type="submit">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">Belum ada data pelanggan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
