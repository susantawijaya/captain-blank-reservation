@extends('layouts.admin')

@section('title', 'Tambah Destinasi')

@section('content')
<div class="card">
    <div class="card-body">
        @include('admin.destinations.partials.form', [
            'action' => route('admin.destinations.store'),
            'method' => 'POST',
            'submitLabel' => 'Simpan Destinasi',
        ])
    </div>
</div>
@endsection
