<section class="relative z-10 -mt-14 pb-6 md:-mt-16">
    <div class="container">
        @include('reservations.partials.availability-search', [
            'action' => route('packages.index'),
            'formId' => 'home_availability',
            'heading' => 'Ketersediaan',
            'title' => 'Tentukan Tanggal dan Jumlah Peserta.',
            'description' => 'Setelah menekan Check Availability, sistem akan menampilkan paket yang sesuai dengan tanggal reservasi dan jumlah peserta yang Anda pilih.',
            'dateValue' => request('date', now()->addDay()->toDateString()),
            'adultCount' => request('adult_count', 2),
            'childCount' => request('child_count', 0),
            'submitLabel' => 'Check Availability',
            'theme' => 'light',
        ])
    </div>
</section>
