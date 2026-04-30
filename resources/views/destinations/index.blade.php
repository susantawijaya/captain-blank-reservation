@extends('layouts.app')

@section('title', 'Destinasi')

@section('content')
<section class="page-header">
    <div class="container">
        <span class="eyebrow">Destinasi</span>
        <h1>Spot Snorkeling</h1>
        <p>Pilih destinasi yang Anda inginkan, lalu sistem akan menampilkan paket yang cocok untuk spot tersebut.</p>
    </div>
</section>
<section class="section">
    <div class="container">
        @include('destinations.partials.destination-filter')
        <div class="grid four">
            @forelse($destinations as $destination)
                @include('destinations.partials.destination-card', ['destination' => $destination])
            @empty
                <div class="card" style="grid-column: 1 / -1;">
                    <div class="card-body">
                        <h3 class="text-xl font-black text-slate-950">Destinasi tidak ditemukan</h3>
                        <p class="mt-2 leading-7 text-slate-600">Coba ubah kata kunci atau pilihan tingkat kesulitan yang Anda gunakan.</p>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</section>
@endsection
