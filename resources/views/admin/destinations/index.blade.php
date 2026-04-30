@extends('layouts.admin')

@section('title', 'Kelola Destinasi')
@section('admin_topbar_actions')
    <a class="button primary" href="{{ route('admin.destinations.create') }}">Tambah Destinasi</a>
@endsection

@section('content')
<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>Nama Destinasi</th>
                <th>Tingkat</th>
                <th>Status</th>
                <th>Deskripsi</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($destinations as $destination)
                <tr>
                    <td>{{ $destination->name }}</td>
                    <td>{{ ucfirst($destination->difficulty) }}</td>
                    <td><x-status-badge :status="$destination->status" /></td>
                    <td>{{ \Illuminate\Support\Str::limit($destination->description, 120) }}</td>
                    <td>
                        <div class="flex flex-wrap gap-3">
                            <a href="{{ route('admin.destinations.edit', $destination) }}">Edit</a>
                            <form method="POST" action="{{ route('admin.destinations.destroy', $destination) }}" onsubmit="return confirm('Hapus destinasi {{ $destination->name }}?');">
                                @csrf
                                @method('DELETE')
                                <button class="text-sm font-bold text-red-600" type="submit">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">Belum ada data destinasi.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
