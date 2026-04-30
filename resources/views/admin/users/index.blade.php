@extends('layouts.admin')

@section('title', 'Manajemen Admin')
@section('admin_topbar_actions')
    <a class="button primary" href="{{ route('admin.users.create') }}">Tambah Admin</a>
@endsection

@section('content')
<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>Nama</th>
                <th>Email</th>
                <th>WhatsApp</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($users as $user)
                <tr>
                    <td>
                        <strong>{{ $user->name }}</strong>
                        @if ($user->isMasterAdmin())
                            <div class="mt-1 text-xs font-bold uppercase tracking-wide text-amber-700">Master Admin</div>
                        @endif
                    </td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->phone ?: '-' }}</td>
                    <td>{{ $user->isMasterAdmin() ? 'Master admin' : 'Admin aktif' }}</td>
                    <td>
                        @if ($user->isMasterAdmin())
                            <span class="text-sm text-slate-500">Terkunci</span>
                        @else
                            <div class="flex flex-wrap gap-3">
                                <a href="{{ route('admin.users.edit', $user) }}">Edit</a>
                                <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Hapus akun {{ $user->name }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-sm font-bold text-red-600" type="submit">Hapus</button>
                                </form>
                            </div>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">Belum ada data admin.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
