@extends('layouts.admin')

@section('title', 'Kelola Jadwal')
@section('admin_topbar_actions')
    <a class="button primary" href="{{ route('admin.schedules.create') }}">Tambah Jadwal</a>
@endsection

@section('content')
<div class="card" style="margin-bottom: 24px;">
    <div class="card-body">
        <form class="form-grid" method="GET" action="{{ route('admin.schedules.index') }}" data-auto-filter-form data-auto-submit-delay="200">
            <div class="grid gap-4 md:grid-cols-3">
                <div class="field">
                    <label for="q">Cari berdasarkan paket</label>
                    <input id="q" name="q" type="text" value="{{ $filters['q'] ?? '' }}" placeholder="Contoh: Lembongan Morning Escape" data-auto-filter-input>
                </div>
                <div class="field">
                    <label for="date">Cari berdasarkan tanggal</label>
                    <input id="date" name="date" type="date" value="{{ $filters['date'] ?? '' }}" data-auto-filter-change>
                </div>
                <div class="field">
                    <label for="status">Cari berdasarkan status</label>
                    <select id="status" name="status" data-auto-filter-change>
                        <option value="all" @selected(($filters['status'] ?? 'all') === 'all')>Semua status</option>
                        <option value="tersedia" @selected(($filters['status'] ?? 'all') === 'tersedia')>Tersedia</option>
                        <option value="penuh" @selected(($filters['status'] ?? 'all') === 'penuh')>Penuh</option>
                        <option value="selesai" @selected(($filters['status'] ?? 'all') === 'selesai')>Selesai</option>
                        <option value="batal_cuaca" @selected(($filters['status'] ?? 'all') === 'batal_cuaca')>Batal Cuaca</option>
                        <option value="reschedule" @selected(($filters['status'] ?? 'all') === 'reschedule')>Reschedule</option>
                    </select>
                </div>
            </div>
        </form>
    </div>
</div>
<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>Paket</th>
                <th>Tanggal</th>
                <th>Jam</th>
                <th>Ketersediaan Kapal</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($schedules as $schedule)
                <tr>
                    <td>{{ $schedule->package->name }}</td>
                    <td>{{ $schedule->start_at->translatedFormat('d M Y') }}</td>
                    <td>{{ $schedule->start_at->format('H:i') }} - {{ $schedule->end_at->format('H:i') }}</td>
                    <td>
                        {{ $schedule->availableBoats() }} / {{ $schedule->boat_count }} kapal
                        <div class="mt-1 text-xs text-slate-500">Maks. {{ $schedule->capacity }} orang per kapal</div>
                        <div class="mt-1 text-xs font-semibold text-sky-700">{{ $schedule->boatAvailabilityCategoryLabel() }}</div>
                    </td>
                    <td><x-status-badge :status="$schedule->status" /></td>
                    <td>
                        <div class="flex flex-wrap gap-3">
                            <a href="{{ route('admin.schedules.edit', $schedule) }}">Edit</a>
                            <form method="POST" action="{{ route('admin.schedules.destroy', $schedule) }}" onsubmit="return confirm('Hapus jadwal ini?');">
                                @csrf
                                @method('DELETE')
                                <button class="text-sm font-bold text-red-600" type="submit">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">Belum ada data jadwal.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
