@php($user = auth()->user())

<footer class="footer">
    <div class="container footer-grid">
        <div>
            <div class="brand text-white">
                <x-brand-logo />
                <span>Captain Blank</span>
            </div>
            <p class="mt-4 max-w-md leading-7 text-sky-100/80">Website reservasi snorkeling Captain Blank untuk cek ketersediaan, memilih destinasi, memesan paket, dan memantau status trip.</p>
        </div>
        <div>
            <h4 class="font-black text-white">Menu</h4>
            <p class="mt-3"><a class="hover:text-white" href="{{ route('home') }}">Beranda</a></p>
            <p class="mt-2"><a class="hover:text-white" href="{{ route('packages.index') }}">Paket Snorkeling</a></p>
            <p class="mt-2"><a class="hover:text-white" href="{{ route('destinations.index') }}">Destinasi</a></p>
            <p class="mt-2"><a class="hover:text-white" href="{{ route('reviews.index') }}">Review</a></p>
        </div>
        <div>
            <h4 class="font-black text-white">Akses</h4>
            @auth
                <p class="mt-3">
                    <a class="hover:text-white" href="{{ $user?->isAdmin() ? route('admin.dashboard') : route('customer.dashboard') }}">
                        {{ $user?->isAdmin() ? 'Dashboard Admin' : 'Dashboard Pelanggan' }}
                    </a>
                </p>
                <p class="mt-2 text-sky-100/80">Login sebagai {{ $user?->email }}</p>
            @else
                <p class="mt-3"><a class="hover:text-white" href="{{ route('login') }}">Login</a></p>
                <p class="mt-2"><a class="hover:text-white" href="{{ route('register') }}">Buat Akun</a></p>
            @endauth
        </div>
    </div>
</footer>
