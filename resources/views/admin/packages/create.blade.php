@extends('layouts.admin')

@section('title', 'Tambah Paket')

@section('content')
<div class="card">
    <div class="card-body">
        @include('admin.packages.partials.form', [
            'action' => route('admin.packages.store'),
            'method' => 'POST',
            'submitLabel' => 'Simpan Paket',
        ])
    </div>
</div>
@endsection
