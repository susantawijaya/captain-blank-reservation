@extends('layouts.admin')

@section('title', 'Edit Destinasi')

@section('content')
<div class="card">
    <div class="card-body">
        @include('admin.destinations.partials.form', [
            'destination' => $destination,
            'action' => route('admin.destinations.update', $destination),
            'method' => 'PUT',
            'submitLabel' => 'Perbarui Destinasi',
            'showDelete' => true,
            'deleteAction' => route('admin.destinations.destroy', $destination),
        ])
    </div>
</div>
@endsection
