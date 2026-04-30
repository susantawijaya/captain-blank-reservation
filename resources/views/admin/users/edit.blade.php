@extends('layouts.admin')

@section('title', 'Edit Admin')

@section('content')
<div class="card">
    <div class="card-body">
        @include('admin.users.partials.form', [
            'action' => route('admin.users.update', $user),
            'method' => 'PUT',
            'submitLabel' => 'Perbarui Admin',
        ])
    </div>
</div>
@endsection
