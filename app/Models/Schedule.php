<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    protected $fillable = [
        'snorkeling_package_id',
        'start_at',
        'end_at',
        'capacity',
        'boat_count',
        'booked_count',
        'status',
        'weather_note',
        'destination_note',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'capacity' => 'integer',
        'boat_count' => 'integer',
        'booked_count' => 'integer',
    ];

    public function package()
    {
        return $this->belongsTo(SnorkelingPackage::class, 'snorkeling_package_id');
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function remainingSlots(): int
    {
        return $this->availableBoats();
    }

    public function hasRemainingSlots(): bool
    {
        return $this->status === 'tersedia' && $this->availableBoats() > 0;
    }

    public function availableBoats(): int
    {
        if ($this->status === 'penuh') {
            return 0;
        }

        if ($this->status !== 'tersedia') {
            return 0;
        }

        return max(0, $this->boat_count - $this->booked_count);
    }

    public function isOpenForBooking(): bool
    {
        return $this->status === 'tersedia'
            && $this->start_at->isFuture()
            && $this->availableBoats() > 0;
    }

    public function boatAvailabilityLabel(): string
    {
        return 'Tersedia '.$this->availableBoats().' dari '.$this->boat_count.' kapal';
    }

    public function boatAvailabilityCategory(): string
    {
        if ($this->status === 'penuh' || $this->availableBoats() <= 0) {
            return 'penuh';
        }

        if ($this->availableBoats() === 1) {
            return 'hampir_penuh';
        }

        return 'tersedia';
    }

    public function boatAvailabilityCategoryLabel(): string
    {
        return match ($this->boatAvailabilityCategory()) {
            'hampir_penuh' => 'Hampir Penuh',
            'penuh' => 'Penuh',
            default => 'Tersedia',
        };
    }
}
