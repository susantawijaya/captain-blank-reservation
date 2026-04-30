@extends('layouts.app')

@section('title', 'Jadwal Snorkeling')

@section('content')
<section class="page-header">
    <div class="container">
        <span class="eyebrow">Jadwal</span>
        <h1>Jadwal Kegiatan</h1>
        <p>Jadwal kegiatan dengan status tersedia, penuh, selesai, batal cuaca, dan reschedule.</p>
    </div>
</section>
<section class="section">
    <div class="container">
        @include('schedules.partials.schedule-filter')
        <div class="grid three">
            @forelse($schedules as $schedule)
                @include('schedules.partials.schedule-card', ['schedule' => $schedule])
            @empty
                <div class="card" style="grid-column: 1 / -1;">
                    <div class="card-body">
                        <h3 class="text-xl font-black text-slate-950">Jadwal tidak ditemukan</h3>
                        <p class="mt-2 leading-7 text-slate-600">Coba ubah kata kunci atau pilihan status jadwal yang Anda gunakan.</p>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</section>
@endsection
