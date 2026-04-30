@extends('layouts.admin')

@section('title', 'Detail Pesan Masuk')

@section('content')
<div class="grid gap-6 xl:grid-cols-[1fr_0.9fr]">
    <div class="card">
        <div class="card-body">
            <h1>{{ $complaint->subject }}</h1>
            <p><strong>Pengirim:</strong> {{ $complaint->user?->name ?: $complaint->guest_name ?: 'Pengunjung umum' }}</p>
            @if ($complaint->user?->email)
                <p><strong>Email:</strong> {{ $complaint->user->email }}</p>
            @endif
            @if ($complaint->guest_phone)
                <p><strong>WhatsApp:</strong> {{ $complaint->guest_phone }}</p>
            @endif
            <p><strong>Status:</strong> <x-status-badge :status="$complaint->status" /></p>
            @if ($complaint->reservation)
                <p><strong>Reservasi:</strong> {{ $complaint->reservation->code }}</p>
            @endif
            <p class="mt-4 leading-7 text-slate-700">{{ $complaint->message }}</p>
            @if ($complaint->admin_reply)
                <div class="mt-6 rounded-2xl border border-emerald-200 bg-emerald-50 p-4">
                    <p class="text-sm font-bold text-emerald-700">Balasan Admin</p>
                    <p class="mt-2 leading-7 text-slate-700">{{ $complaint->admin_reply }}</p>
                    @if ($complaint->replied_at)
                        <p class="mt-3 text-sm text-slate-500">Dibalas pada {{ $complaint->replied_at->format('d M Y H:i') }}</p>
                    @endif
                </div>
            @endif
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <h2>Kelola Pesan</h2>
            @unless ($complaint->user_id)
                <div class="mt-4 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                    Pesan ini dikirim tanpa login. Balasan admin tetap akan tersimpan di sistem, tetapi pengunjung tidak bisa melihatnya dari portal pelanggan. Gunakan nomor WhatsApp atau kontak lain jika ingin menghubungi langsung.
                </div>
            @endunless
            <form class="form-grid mt-4" method="POST" action="{{ route('admin.complaints.update', $complaint) }}">
                @csrf
                @method('PUT')
                <div class="field">
                    <label for="status">Status Pesan</label>
                    <select id="status" name="status" required>
                        <option value="baru" @selected(old('status', $complaint->status) === 'baru')>Baru</option>
                        <option value="diproses" @selected(old('status', $complaint->status) === 'diproses')>Diproses</option>
                        <option value="selesai" @selected(old('status', $complaint->status) === 'selesai')>Selesai</option>
                    </select>
                    @error('status')
                        <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div class="field">
                    <label for="admin_reply">Balasan Admin</label>
                    <textarea id="admin_reply" name="admin_reply" rows="7" placeholder="Tulis balasan untuk pelanggan di sini">{{ old('admin_reply', $complaint->admin_reply) }}</textarea>
                    @error('admin_reply')
                        <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div class="flex flex-wrap gap-3">
                    <button class="button primary" type="submit">Simpan Status</button>
                    <button class="button secondary !border-red-200 !text-red-700 hover:!bg-red-50" type="submit" form="delete-complaint-form">Hapus</button>
                </div>
            </form>
            <form id="delete-complaint-form" method="POST" action="{{ route('admin.complaints.destroy', $complaint) }}" onsubmit="return confirm('Hapus pesan ini?');">
                @csrf
                @method('DELETE')
            </form>
        </div>
    </div>
</div>
@endsection
