@extends('layouts.app')

@section('title', 'Galeri')

@section('content')
<section class="page-header">
    <div class="container">
        <span class="eyebrow">Galeri</span>
        <h1>Dokumentasi Trip</h1>
        <p>Dokumentasi kegiatan snorkeling dan perjalanan pelanggan.</p>
    </div>
</section>
<section class="section">
    <div class="container grid four">
        @foreach($galleryItems as $item)
            @include('gallery.partials.gallery-card', ['item' => $item])
        @endforeach
    </div>
</section>
@endsection
