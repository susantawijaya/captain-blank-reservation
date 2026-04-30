@extends('layouts.admin')

@section('title', 'Pesan Masuk')

@section('content')
<div class="card" style="margin-bottom: 16px;">
    <div class="card-body">
        <form class="form-grid" method="GET" action="{{ route('admin.complaints.index') }}" data-auto-filter-form data-auto-submit-delay="200">
            <div class="field">
                <label for="status">Filter status</label>
                <select id="status" name="status" data-auto-filter-change>
                    <option value="all" @selected(($filters['status'] ?? 'all') === 'all')>Semua status</option>
                    <option value="baru" @selected(($filters['status'] ?? 'all') === 'baru')>Baru</option>
                    <option value="diproses" @selected(($filters['status'] ?? 'all') === 'diproses')>Diproses</option>
                    <option value="selesai" @selected(($filters['status'] ?? 'all') === 'selesai')>Selesai</option>
                </select>
            </div>
        </form>
    </div>
</div>

<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>Pelanggan</th>
                <th>Subjek</th>
                <th>Status</th>
                <th>Balasan</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($complaints as $complaint)
                <tr>
                    <td>
                        {{ $complaint->user?->name ?: $complaint->guest_name ?: 'Pengunjung umum' }}
                        @if ($complaint->guest_phone)
                            <div class="mt-1 text-sm text-slate-500">{{ $complaint->guest_phone }}</div>
                        @endif
                    </td>
                    <td>{{ $complaint->subject }}</td>
                    <td><x-status-badge :status="$complaint->status" /></td>
                    <td>
                        @if ($complaint->admin_reply)
                            <span class="badge">Sudah Dibalas</span>
                        @else
                            <span class="text-sm text-slate-500">Belum dibalas</span>
                        @endif
                    </td>
                    <td>
                        <div class="flex flex-wrap gap-3">
                            <a href="{{ route('admin.complaints.show', $complaint) }}">Buka</a>
                            <form method="POST" action="{{ route('admin.complaints.destroy', $complaint) }}" onsubmit="return confirm('Hapus pesan ini?');">
                                @csrf
                                @method('DELETE')
                                <button class="text-sm font-bold text-red-600" type="submit">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">Belum ada pesan masuk.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
