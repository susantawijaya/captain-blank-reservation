@extends('layouts.admin')

@section('title', 'Kelola Review')

@section('content')
<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>Pelanggan</th>
                <th>Paket</th>
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
                    <td colspan="6">Belum ada review pelanggan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
