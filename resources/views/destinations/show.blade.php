@extends('layouts.app')

@section('title', $destination->name)

@section('content')
<section class="page-header">
    <div class="container">
        <span class="eyebrow">Detail destinasi</span>
        <h1>{{ $destination->name }}</h1>
        <p>{{ $destination->description }}</p>
    </div>
</section>
<section class="section">
    <div class="container">
        <div class="grid three">
            @foreach($destination->packages as $package)
                @include('packages.partials.package-card', ['package' => $package])
            @endforeach
        </div>
    </div>
</section>
@endsection
