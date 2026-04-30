@extends('layouts.admin')

@section('title', 'Edit FAQ')

@section('content')
<div class="card">
    <div class="card-body">
        @include('admin.faqs.partials.form', [
            'faq' => $faq,
            'action' => route('admin.faqs.update', $faq),
            'method' => 'PUT',
            'submitLabel' => 'Perbarui FAQ',
            'showDelete' => true,
            'deleteAction' => route('admin.faqs.destroy', $faq),
        ])
    </div>
</div>
@endsection
