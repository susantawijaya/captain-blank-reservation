<h3>Ringkasan Pembayaran</h3>
<p>Status awal reservasi akan menjadi <x-status-badge status="menunggu_pembayaran" />.</p>
<p>Pelanggan transfer manual ke rekening yang tersedia, lalu upload bukti pembayaran.</p>
<hr>
@if($company?->bank_name && $company?->bank_account_number)
    <p><strong>Bank:</strong> {{ $company->bank_name }} {{ $company->bank_account_number }} a.n. {{ $company->bank_account_name }}</p>
@else
    <p>Informasi rekening pembayaran belum tersedia.</p>
@endif
