@extends('layouts.guest')

@section('title', 'Reset Password - Captain Blank')
@section('auth_eyebrow', 'Reset Akses')
@section('auth_heading', 'Reset Password')
@section('auth_description', 'Masukkan email akun Anda untuk menerima tautan pembuatan password baru dengan tampilan UI yang sama seperti portal utama.')
@section('auth_showcase_title', 'Pemulihan akun lebih rapi')
@section('auth_showcase_text', 'Halaman reset password sekarang mengikuti warna, spacing, dan struktur panel yang seragam dengan login serta dashboard.')

@section('content')
<div class="auth-form-intro">
    <h2>Kirim tautan reset</h2>
    <p>Masukkan email akun aktif Anda. Sistem akan mengirim tautan untuk membuat password baru.</p>
</div>

<form class="form-grid" method="POST" action="{{ route('password.email') }}">
    @csrf
    <div class="field">
        <label for="email">Email</label>
        <input id="email" name="email" type="email" value="{{ old('email') }}" placeholder="email@domain.com" required autofocus>
        @error('email')
            <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
        @enderror
    </div>
    <button class="button primary" type="submit">Kirim Tautan Reset</button>
</form>

<div class="auth-links">
    <a class="button secondary" href="{{ route('login') }}">Kembali ke Login</a>
</div>
@endsection
