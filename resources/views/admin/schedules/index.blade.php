@extends('layouts.admin')

@section('title', 'Kelola Jadwal')
@section('admin_topbar_actions')
    <a class="button primary" href="{{ route('admin.schedules.create') }}">Tambah Jadwal</a>
@endsection

@section('content')
<div class="card" style="margin-bottom: 24px;">
    <div class="card-body">
        <form class="form-grid" method="GET" action="{{ route('admin.schedules.index') }}" data-auto-filter-form data-auto-submit-delay="200">
            <div class="field">
                <label for="schedule_order">Urutkan Jadwal Berdasarkan</label>
                <select id="schedule_order" name="schedule_order" data-auto-filter-change>
                    <option value="package" @selected(($filters['schedule_order'] ?? 'date') === 'package')>Nama Paket</option>
                    <option value="date" @selected(($filters['schedule_order'] ?? 'date') === 'date')>Tanggal</option>
                    <option value="time" @selected(($filters['schedule_order'] ?? 'date') === 'time')>Jam</option>
                    <option value="availability" @selected(($filters['schedule_order'] ?? 'date') === 'availability')>Ketersediaan</option>
                </select>
                <p class="mt-2 text-sm text-slate-600">Pilih satu mode urutan agar tabel langsung disusun ulang tanpa memenuhi area filter.</p>
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
