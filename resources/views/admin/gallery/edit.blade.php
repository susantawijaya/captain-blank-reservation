@extends('layouts.admin')

@section('title', 'Edit Galeri')

@section('content')
<div class="card">
    <div class="card-body">
        @include('admin.gallery.partials.form', [
            'galleryItem' => $galleryItem,
            'action' => route('admin.gallery.update', $galleryItem),
            'method' => 'PUT',
            'submitLabel' => 'Perbarui Galeri',
            'showDelete' => true,
            'deleteAction' => route('admin.gallery.destroy', $galleryItem),
        ])
    </div>
</div>
@endsection
