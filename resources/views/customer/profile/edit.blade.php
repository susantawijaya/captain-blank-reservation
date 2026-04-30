@extends('layouts.customer')

@section('title', 'Edit Profil')
@section('customer_badge', 'Edit Profil')
@section('customer_intro', 'Perbarui data pelanggan agar kontak dan identitas akun Anda selalu akurat.')
@section('customer_actions')
    <a class="button secondary" href="{{ route('customer.profile.index') }}">Lihat Profil</a>
    <a class="button primary" href="{{ route('customer.reservations.index') }}">Reservasi Saya</a>
@endsection

@section('customer_content')
<div class="grid gap-6 xl:grid-cols-[1fr_0.7fr]">
    <div class="card">
        <div class="card-body">
            <form class="form-grid" method="POST" action="{{ route('customer.profile.update') }}">
                @csrf
                @method('PUT')
                <div class="field">
                    <label for="name">Nama</label>
                    <input id="name" name="name" type="text" value="{{ old('name', $user?->name) }}" required>
                    @error('name')
                        <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div class="field">
                    <label for="email">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email', $user?->email) }}" required>
                    @error('email')
                        <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div class="field">
                    <label for="phone">WhatsApp</label>
                    <input id="phone" name="phone" type="text" value="{{ old('phone', $user?->phone) }}" required>
                    @error('phone')
                        <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div class="field">
                    <label for="address">Alamat</label>
                    <textarea id="address" name="address" placeholder="Alamat lengkap">{{ old('address', $user?->address) }}</textarea>
                    @error('address')
                        <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <button class="button primary" type="submit">Simpan</button>
            </form>
        </div>
    </div>
    <div class="soft-panel">
        <h2>Yang Perlu Dicek</h2>
        <p class="mt-3 text-slate-600">Pastikan email dan nomor WhatsApp aktif. Kedua data ini paling sering dipakai untuk verifikasi reservasi dan komunikasi lanjutan dari admin.</p>
    </div>
</div>
@endsection
