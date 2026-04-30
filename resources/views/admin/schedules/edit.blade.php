@extends('layouts.admin')

@section('title', 'Edit Jadwal')

@section('content')
<div class="card">
    <div class="card-body">
        @include('admin.schedules.partials.form', [
            'schedule' => $schedule,
            'action' => route('admin.schedules.update', $schedule),
            'method' => 'PUT',
            'submitLabel' => 'Perbarui Jadwal',
            'showDelete' => true,
            'deleteAction' => route('admin.schedules.destroy', $schedule),
        ])
    </div>
</div>
@endsection
