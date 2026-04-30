@extends('layouts.customer')

@section('title', 'Pesan Saya')
@section('customer_badge', 'Pesan Pelanggan')
@section('customer_intro', 'Semua pertanyaan yang pernah Anda kirim ke admin tersimpan di sini lengkap dengan status dan balasannya.')
@section('customer_actions')
    <a class="button primary" href="{{ route('contact.index') }}">Kirim Pesan Baru</a>
@endsection

@section('customer_content')
<div class="customer-page-stack">
    <div class="section-head">
        <div>
            <h2>Inbox Pelanggan</h2>
            <p>Balasan admin akan muncul pada detail setiap pesan, jadi Anda bisa memantau semuanya dari satu tabel.</p>
        </div>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Subjek</th>
                    <th>Status</th>
                    <th>Balasan</th>
                    <th>Tanggal</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($messages as $message)
                    <tr>
                        <td>{{ $message->subject }}</td>
                        <td><x-status-badge :status="$message->status" /></td>
                        <td>
                            @if($message->admin_reply)
                                <span class="badge">Sudah Dibalas</span>
                            @else
                                <span class="text-sm text-slate-500">Menunggu balasan</span>
                            @endif
                        </td>
                        <td>{{ $message->created_at->format('d M Y H:i') }}</td>
                        <td><a href="{{ route('customer.messages.show', $message) }}">Buka</a></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">Belum ada pesan yang dikirim dari akun Anda.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
