<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    protected $fillable = [
        'code',
        'user_id',
        'snorkeling_package_id',
        'destination_id',
        'schedule_id',
        'booking_date',
        'participants',
        'adult_count',
        'child_count',
        'contact_name',
        'contact_phone',
        'pickup_location',
        'total_price',
        'status',
        'notes',
    ];

    protected $casts = [
        'booking_date' => 'date',
        'participants' => 'integer',
        'adult_count' => 'integer',
        'child_count' => 'integer',
        'total_price' => 'integer',
    ];

    protected static function booted(): void
    {
        static::saving(function (Reservation $reservation) {
            $adultCount = $reservation->adult_count;
            $childCount = $reservation->child_count;

            if ($adultCount === null && $reservation->participants !== null) {
                $adultCount = (int) $reservation->participants;
            }

            $adultCount = max(0, (int) ($adultCount ?? 0));
            $childCount = max(0, (int) ($childCount ?? 0));
            $participants = $adultCount + $childCount;

            if ($participants < 1) {
                $participants = max(1, (int) ($reservation->participants ?? 1));
                $adultCount = $participants;
                $childCount = 0;
            }

            $reservation->adult_count = $adultCount;
            $reservation->child_count = $childCount;
            $reservation->participants = $participants;
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function package()
    {
        return $this->belongsTo(SnorkelingPackage::class, 'snorkeling_package_id');
    }

    public function destination()
    {
        return $this->belongsTo(Destination::class);
    }

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    public function review()
    {
        return $this->hasOne(Review::class);
    }

    public function isCustomerEditable(): bool
    {
        $payment = $this->payment;

        if (! $payment) {
            return in_array($this->status, ['menunggu_pembayaran', 'dijadwalkan_ulang'], true);
        }

        if ($payment->status === 'ditolak' && $this->status === 'menunggu_pembayaran') {
            return true;
        }

        if ($this->status === 'dijadwalkan_ulang' && in_array($payment->status, ['belum_bayar', 'ditolak'], true)) {
            return true;
        }

        return $this->status === 'menunggu_pembayaran'
            && $payment->status === 'belum_bayar'
            && empty($payment->proof_image);
    }

    public function canCustomerUploadPayment(): bool
    {
        $payment = $this->payment;

        if (! $payment) {
            return $this->status === 'menunggu_pembayaran';
        }

        if ($payment->status === 'ditolak' && $this->status === 'menunggu_pembayaran') {
            return true;
        }

        return $this->status === 'menunggu_pembayaran'
            && $payment->status === 'belum_bayar'
            && empty($payment->proof_image);
    }

    public function participantBreakdown(): string
    {
        return $this->adult_count.' dewasa, '.$this->child_count.' anak';
    }

    public function hasRejectedPayment(): bool
    {
        return $this->payment?->status === 'ditolak' && $this->status === 'menunggu_pembayaran';
    }

    public function displayStatusLabel(): string
    {
        if ($this->hasRejectedPayment()) {
            return 'Menunggu Pembayaran Ulang';
        }

        return match ($this->status) {
            'menunggu_pembayaran' => 'Menunggu Pembayaran',
            'menunggu_verifikasi' => 'Menunggu Konfirmasi',
            'terkonfirmasi' => 'Terkonfirmasi',
            'selesai' => 'Selesai',
            'dibatalkan' => 'Dibatalkan',
            'dijadwalkan_ulang' => 'Dijadwalkan Ulang',
            default => str_replace('_', ' ', $this->status),
        };
    }

    public function statusContextNote(): ?string
    {
        if ($this->hasRejectedPayment()) {
            return 'Pembayaran sebelumnya ditolak admin. Pelanggan perlu mengunggah ulang bukti pembayaran.';
        }

        if ($this->status === 'dijadwalkan_ulang') {
            return 'Reservasi ini dijadwalkan ulang. Silakan perbarui tanggal atau jam trip sebelum melanjutkan pembayaran.';
        }

        if ($this->status === 'dibatalkan') {
            return 'Reservasi ini telah dibatalkan dan tidak dapat diproses lebih lanjut.';
        }

        return null;
    }
}
