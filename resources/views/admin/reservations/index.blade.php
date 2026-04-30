@extends('layouts.admin')

@section('title', 'Kelola Reservasi')

@section('content')
@include('admin.reservations.partials.filter')
@include('admin.reservations.partials.table', ['reservations' => $reservations])
@endsection
