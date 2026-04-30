@extends('layouts.admin')

@section('title', 'Edit Paket')

@section('content')
<div class="card">
    <div class="card-body">
        @include('admin.packages.partials.form', [
            'package' => $package,
            'action' => route('admin.packages.update', $package),
            'method' => 'PUT',
            'submitLabel' => 'Perbarui Paket',
            'showDelete' => true,
            'deleteAction' => route('admin.packages.destroy', $package),
        ])
    </div>
</div>
@endsection
