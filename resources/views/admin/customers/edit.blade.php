@extends('layouts.admin')

@section('title', 'Edit Pelanggan')

@section('content')
<div class="card">
    <div class="card-body">
        @include('admin.customers.partials.form', [
            'action' => route('admin.customers.update', $customer),
            'method' => 'PUT',
            'submitLabel' => 'Perbarui Pelanggan',
        ])
    </div>
</div>
@endsection
