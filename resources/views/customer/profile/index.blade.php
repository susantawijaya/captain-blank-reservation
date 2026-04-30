@extends('layouts.customer')

@section('title', 'Profil Pelanggan')
@section('customer_badge', 'Profil Akun')
@section('customer_intro', 'Data kontak dan identitas akun pelanggan Anda tersimpan di sini agar admin mudah menghubungi saat dibutuhkan.')
@section('customer_actions')
    <a class="button secondary" href="{{ route('customer.reservations.index') }}">Reservasi Saya</a>
    <a class="button primary" href="{{ route('customer.profile.edit') }}">Edit Profil</a>
@endsection

@section('customer_content')
<div class="grid gap-6 xl:grid-cols-[1.1fr_0.9fr]">
    <div class="card">
        <div class="card-body">
            <h2>Informasi Akun</h2>
            <div class="info-list mt-5">
                <div class="info-item">
                    <span>Nama</span>
                    <strong>{{ $user?->name }}</strong>
                </div>
                <div class="info-item">
                    <span>Email</span>
                    <strong>{{ $user?->email }}</strong>
                </div>
                <div class="info-item">
                    <span>WhatsApp</span>
                    <strong>{{ $user?->phone ?: '-' }}</strong>
                </div>
                <div class="info-item">
                    <span>Alamat</span>
                    <strong>{{ $user?->address ?: '-' }}</strong>
                </div>
            </div>
        </div>
    </div>
    <div class="soft-panel">
        <h2>Kenapa Profil Penting?</h2>
        <p class="mt-3 text-slate-600">Data profil yang rapi membantu admin mengonfirmasi reservasi, menghubungi Anda saat ada perubahan jadwal, dan mencocokkan pembayaran dengan lebih cepat.</p>
        <div class="mt-5 flex flex-wrap gap-3">
            <a class="button secondary" href="{{ route('customer.reservations.index') }}">Lihat Reservasi</a>
            <a class="button primary" href="{{ route('customer.profile.edit') }}">Perbarui Profil</a>
        </div>
    </div>
</div>
@endsection
