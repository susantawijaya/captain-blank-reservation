@extends('layouts.customer')

@section('title', 'Profil Pelanggan')
@section('customer_badge', 'Profil Akun')
@section('customer_intro', 'Data kontak dan identitas akun pelanggan Anda tersimpan di sini agar admin mudah menghubungi saat dibutuhkan.')
@section('customer_actions')
    <a class="button primary" href="{{ route('customer.profile.edit') }}">Edit Profil</a>
@endsection

@section('customer_content')
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
@endsection
