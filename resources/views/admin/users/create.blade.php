@extends('layouts.admin')

@section('title', 'Tambah Admin')

@section('content')
<div class="card">
    <div class="card-body">
        @include('admin.users.partials.form', [
            'action' => route('admin.users.store'),
            'method' => 'POST',
            'submitLabel' => 'Simpan Admin',
        ])
    </div>
</div>
@endsection
