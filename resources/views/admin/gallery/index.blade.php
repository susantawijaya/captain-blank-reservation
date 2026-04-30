@extends('layouts.admin')

@section('title', 'Kelola Galeri')
@section('admin_topbar_actions')
    <a class="button primary" href="{{ route('admin.gallery.create') }}">Tambah Foto</a>
@endsection

@section('content')
<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>Judul</th>
                <th>Kategori</th>
                <th>Unggulan</th>
                <th>Caption</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($galleryItems as $item)
                <tr>
                    <td>{{ $item->title }}</td>
                    <td>{{ $item->category }}</td>
                    <td>{{ $item->is_featured ? 'Ya' : 'Tidak' }}</td>
                    <td>{{ \Illuminate\Support\Str::limit($item->caption, 100) }}</td>
                    <td>
                        <div class="flex flex-wrap gap-3">
                            <a href="{{ route('admin.gallery.edit', $item) }}">Edit</a>
                            <form method="POST" action="{{ route('admin.gallery.destroy', $item) }}" onsubmit="return confirm('Hapus data galeri {{ $item->title }}?');">
                                @csrf
                                @method('DELETE')
                                <button class="text-sm font-bold text-red-600" type="submit">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">Belum ada data galeri.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
