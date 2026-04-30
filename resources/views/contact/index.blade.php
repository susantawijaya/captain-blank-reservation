@extends('layouts.app')

@section('title', 'Kontak')

@section('content')
<section class="page-header">
    <div class="container">
        <span class="eyebrow">Kontak</span>
        <h1>Hubungi Captain Blank</h1>
        <p>Gunakan halaman ini untuk mengirim pertanyaan atau kebutuhan informasi reservasi langsung ke admin Captain Blank.</p>
    </div>
</section>
<section class="section">
    <div class="container grid gap-6 lg:grid-cols-2">
        <div class="card">
            <div class="card-body">
                @auth
                    @if (auth()->user()?->isCustomer())
                        <div class="mb-5 rounded-2xl border border-sky-200 bg-sky-50 p-4 text-sm text-sky-800">
                            Jika Anda mengirim pesan saat login, balasan admin akan muncul di
                            <a class="font-bold hover:text-sky-900" href="{{ route('customer.messages.index') }}">Pesan Saya</a>.
                        </div>
                        @include('contact.partials.contact-form')
                    @else
                        <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5 text-sm text-amber-900">
                            Akun admin tidak menggunakan form kontak pelanggan. Silakan kelola pesan dari dashboard admin.
                            <div class="mt-4">
                                <a class="button secondary" href="{{ route('admin.dashboard') }}">Kembali ke Dashboard Admin</a>
                            </div>
                        </div>
                    @endif
                @else
                    <div class="rounded-2xl border border-sky-200 bg-sky-50 p-5 text-sm text-sky-800">
                        Untuk mengirim pesan dan melihat balasan admin, Anda perlu login dulu sebagai pelanggan.
                        <div class="mt-4 flex flex-wrap gap-3">
                            <a class="button primary" href="{{ route('login') }}">Login</a>
                            <a class="button secondary" href="{{ route('register') }}">Buat Akun</a>
                        </div>
                    </div>
                @endauth
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <h3>Informasi Usaha</h3>
                <p>{{ $company?->address }}</p>
                <p>WhatsApp: {{ $company?->whatsapp }}</p>
                <p>Bank: {{ $company?->bank_name }} {{ $company?->bank_account_number }}</p>
                <h3>FAQ</h3>
                @foreach($faqs as $faq)
                    <p><strong>{{ $faq->question }}</strong><br>{{ $faq->answer }}</p>
                @endforeach
            </div>
        </div>
    </div>
</section>
@endsection
