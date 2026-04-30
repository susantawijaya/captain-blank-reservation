@props(['status', 'label' => null])

@php
    $success = ['terkonfirmasi', 'selesai', 'diterima', 'tersedia', 'published'];
    $warning = ['menunggu_pembayaran', 'menunggu_verifikasi', 'dijadwalkan_ulang', 'reschedule', 'baru', 'diproses'];
    $danger = ['dibatalkan', 'ditolak', 'batal_cuaca', 'hidden'];
    $class = in_array($status, $success, true) ? 'success' : (in_array($status, $danger, true) ? 'danger' : (in_array($status, $warning, true) ? 'warning' : ''));
    $labels = [
        'menunggu_pembayaran' => 'Menunggu Pembayaran',
        'menunggu_verifikasi' => 'Menunggu Konfirmasi',
        'terkonfirmasi' => 'Terkonfirmasi',
        'selesai' => 'Selesai',
        'dibatalkan' => 'Dibatalkan',
        'dijadwalkan_ulang' => 'Dijadwalkan Ulang',
        'diterima' => 'Diterima',
        'ditolak' => 'Ditolak',
        'batal_cuaca' => 'Batal Cuaca',
        'reschedule' => 'Reschedule',
        'tersedia' => 'Tersedia',
        'published' => 'Published',
        'hidden' => 'Hidden',
        'baru' => 'Baru',
        'diproses' => 'Diproses',
    ];
@endphp

<span class="badge {{ $class }}">{{ $label ?? ($labels[$status] ?? str_replace('_', ' ', $status)) }}</span>
