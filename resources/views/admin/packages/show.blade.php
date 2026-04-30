@extends('layouts.admin')

@section('title', $package->name)

@section('content')
<div class="card"><div class="card-body"><h1>{{ $package->name }}</h1><p>{{ $package->description }}</p><p>Harga: Rp {{ number_format($package->price, 0, ',', '.') }}</p><a class="button secondary" href="{{ route('admin.packages.edit', $package) }}">Edit</a></div></div>
@endsection
