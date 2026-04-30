@extends('layouts.admin')

@section('title', 'Tambah FAQ')

@section('content')
<div class="card">
    <div class="card-body">
        @include('admin.faqs.partials.form', [
            'faq' => $faq,
            'action' => route('admin.faqs.store'),
            'method' => 'POST',
            'submitLabel' => 'Simpan FAQ',
        ])
    </div>
</div>
@endsection
