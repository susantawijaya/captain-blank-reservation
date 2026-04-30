@extends('layouts.admin')

@section('title', 'Tambah Galeri')

@section('content')
<div class="card">
    <div class="card-body">
        @include('admin.gallery.partials.form', [
            'action' => route('admin.gallery.store'),
            'method' => 'POST',
            'submitLabel' => 'Simpan Galeri',
        ])
    </div>
</div>
@endsection
