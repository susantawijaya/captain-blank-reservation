@extends('layouts.admin')

@section('title', 'Kelola Review')

@section('content')
<div class="card" style="margin-bottom: 16px;">
    <div class="card-body">
        <form class="form-grid" method="GET" action="{{ route('admin.reviews.index') }}" data-auto-filter-form data-auto-submit-delay="200">
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div class="field">
                    <label for="customer">Cari nama pelanggan</label>
                    <input id="customer" name="customer" type="text" value="{{ $filters['customer'] ?? '' }}" placeholder="Contoh: Santa" data-auto-filter-input>
                </div>
                <div class="field">
                    <label for="package">Cari paket</label>
                    <input id="package" name="package" type="text" value="{{ $filters['package'] ?? '' }}" placeholder="Contoh: Morning Escape" data-auto-filter-input>
                </div>
                <div class="field">
                    <label for="date">Cari tanggal</label>
                    <input id="date" name="date" type="date" value="{{ $filters['date'] ?? '' }}" data-auto-filter-change>
                </div>
                <div class="field">
                    <label for="status">Cari status</label>
                    <select id="status" name="status" data-auto-filter-change>
                        <option value="all" @selected(($filters['status'] ?? 'all') === 'all')>Semua status</option>
                        <option value="draft" @selected(($filters['status'] ?? 'all') === 'draft')>Draft</option>
                        <option value="published" @selected(($filters['status'] ?? 'all') === 'published')>Published</option>
                        <option value="hidden" @selected(($filters['status'] ?? 'all') === 'hidden')>Hidden</option>
                    </select>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>Pelanggan</th>
                <th>Paket</th>
                <th>Tanggal Review</th>
                <th>Rating</th>
                <th>Status</th>
                <th>Komentar</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($reviews as $review)
                <tr>
                    <td>{{ $review->user->name }}</td>
                    <td>{{ $review->package->name }}</td>
                    <td>{{ $review->created_at?->translatedFormat('d M Y H:i') ?? '-' }}</td>
                    <td><x-rating-stars :rating="$review->rating" size="sm" /></td>
                    <td><x-status-badge :status="$review->status" /></td>
                    <td>{{ \Illuminate\Support\Str::limit($review->comment, 120) }}</td>
                    <td>
                        <div class="flex flex-wrap gap-3">
                            <a href="{{ route('admin.reviews.show', $review) }}">Buka</a>
                            <form method="POST" action="{{ route('admin.reviews.destroy', $review) }}" onsubmit="return confirm('Hapus review ini?');">
                                @csrf
                                @method('DELETE')
                                <button class="text-sm font-bold text-red-600" type="submit">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">Belum ada review pelanggan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
