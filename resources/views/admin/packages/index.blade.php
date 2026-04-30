@extends('layouts.admin')

@section('title', 'Kelola Paket')
@section('admin_topbar_actions')
    <a class="button primary" href="{{ route('admin.packages.create') }}">Tambah Paket</a>
@endsection

@section('content')
<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>Nama Paket</th>
                <th>Harga</th>
                <th>Durasi</th>
                <th>Kapasitas</th>
                <th>Destinasi</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($packages as $package)
                <tr>
                    <td>
                        <strong>{{ $package->name }}</strong>
                        <div class="mt-1 text-sm text-slate-500">{{ $package->short_description }}</div>
                    </td>
                    <td>Rp {{ number_format($package->price, 0, ',', '.') }}</td>
                    <td>{{ $package->duration }}</td>
                    <td>{{ $package->capacity }} orang</td>
                    <td>{{ $package->destinations->pluck('name')->join(', ') ?: '-' }}</td>
                    <td><x-status-badge :status="$package->status" /></td>
                    <td>
                        <div class="flex flex-wrap gap-3">
                            <a href="{{ route('admin.packages.edit', $package) }}">Edit</a>
                            <form method="POST" action="{{ route('admin.packages.destroy', $package) }}" onsubmit="return confirm('Hapus paket {{ $package->name }}?');">
                                @csrf
                                @method('DELETE')
                                <button class="text-sm font-bold text-red-600" type="submit">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">Belum ada data paket.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
