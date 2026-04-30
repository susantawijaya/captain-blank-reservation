@extends('layouts.admin')

@section('title', 'Tambah Jadwal')

@section('content')
<div class="card">
    <div class="card-body">
        @include('admin.schedules.partials.form', [
            'action' => route('admin.schedules.store'),
            'method' => 'POST',
            'submitLabel' => 'Simpan Jadwal',
        ])
    </div>
</div>
@endsection
