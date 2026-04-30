@extends('layouts.admin')

@section('title', 'Detail Review')

@section('content')
<div class="detail-split-grid grid gap-6 xl:grid-cols-[1fr_0.9fr]">
    <div class="card">
        <div class="card-body">
            <h1>Review {{ $review->user->name }}</h1>
            <p><strong>Paket:</strong> {{ $review->package->name }}</p>
            <p><strong>Rating:</strong> <x-rating-stars :rating="$review->rating" size="sm" show-value /></p>
            <p><strong>Status:</strong> <x-status-badge :status="$review->status" /></p>
            <p class="mt-4 leading-7 text-slate-700">{{ $review->comment }}</p>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <h2>Kelola Review</h2>
            <form class="form-grid mt-4" method="POST" action="{{ route('admin.reviews.update', $review) }}">
                @csrf
                @method('PUT')
                <div class="field">
                    <label for="status">Status Review</label>
                    <select id="status" name="status" required>
                        <option value="draft" @selected(old('status', $review->status) === 'draft')>Draft</option>
                        <option value="published" @selected(old('status', $review->status) === 'published')>Published</option>
                        <option value="hidden" @selected(old('status', $review->status) === 'hidden')>Hidden</option>
                    </select>
                    @error('status')
                        <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div class="detail-action-row flex flex-wrap gap-3">
                    <button class="button primary" type="submit">Simpan Perubahan</button>
                    <button class="button secondary !border-red-200 !text-red-700 hover:!bg-red-50" type="submit" form="delete-review-form">Hapus</button>
                </div>
            </form>
            <form id="delete-review-form" method="POST" action="{{ route('admin.reviews.destroy', $review) }}" onsubmit="return confirm('Hapus review ini?');">
                @csrf
                @method('DELETE')
            </form>
        </div>
    </div>
</div>
@endsection
