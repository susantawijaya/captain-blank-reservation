@extends('layouts.admin')

@section('title', 'FAQ Website')
@section('admin_topbar_actions')
    <a class="button primary" href="{{ route('admin.faqs.create') }}">Tambah FAQ</a>
@endsection

@section('content')
<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>Urutan</th>
                <th>Pertanyaan</th>
                <th>Jawaban</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($faqs as $faq)
                <tr>
                    <td>{{ $faq->sort_order }}</td>
                    <td>{{ $faq->question }}</td>
                    <td>{{ \Illuminate\Support\Str::limit($faq->answer, 120) }}</td>
                    <td>
                        <div class="flex flex-wrap gap-3">
                            <a href="{{ route('admin.faqs.edit', $faq) }}">Edit</a>
                            <form method="POST" action="{{ route('admin.faqs.destroy', $faq) }}" onsubmit="return confirm('Hapus FAQ ini?');">
                                @csrf
                                @method('DELETE')
                                <button class="text-sm font-bold text-red-600" type="submit">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4">Belum ada FAQ.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
