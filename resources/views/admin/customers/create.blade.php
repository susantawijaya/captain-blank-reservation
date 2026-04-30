@extends('layouts.admin')

@section('title', 'Tambah Pelanggan')

@section('content')
<div class="card">
    <div class="card-body">
        @include('admin.customers.partials.form', [
            'action' => route('admin.customers.store'),
            'method' => 'POST',
            'submitLabel' => 'Simpan Pelanggan',
        ])
    </div>
</div>
@endsection
