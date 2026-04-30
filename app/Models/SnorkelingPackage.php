<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SnorkelingPackage extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'short_description',
        'description',
        'price',
        'duration',
        'capacity',
        'facilities',
        'image_path',
        'status',
    ];

    protected $casts = [
        'price' => 'integer',
        'capacity' => 'integer',
    ];

    public function destinations()
    {
        return $this->belongsToMany(Destination::class);
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class, 'snorkeling_package_id');
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class, 'snorkeling_package_id');
    }

    public function publicSchedules()
    {
        return $this->hasMany(Schedule::class, 'snorkeling_package_id')
            ->where('start_at', '>=', now())
            ->whereIn('status', ['tersedia', 'penuh'])
            ->orderBy('start_at');
    }

    public function availabilitySummary(): array
    {
        $schedules = ($this->relationLoaded('schedules') ? $this->schedules : $this->publicSchedules()->get())
            ->sortBy('start_at')
            ->values();

        if ($schedules->isEmpty()) {
            return [
                'label' => 'Habis',
                'variant' => 'danger',
                'note' => 'Belum ada jadwal kapal yang bisa dipesan.',
                'remaining_slots' => 0,
                'next_start_at' => null,
            ];
        }

        $availableSchedules = $schedules
            ->filter(fn (Schedule $schedule) => $schedule->hasRemainingSlots())
            ->values();

        if ($availableSchedules->isEmpty()) {
            return [
                'label' => 'Habis',
                'variant' => 'danger',
                'note' => 'Semua kapal pada jadwal aktif saat ini sudah terpakai.',
                'remaining_slots' => 0,
                'next_start_at' => null,
            ];
        }

        $availableBoatCount = $availableSchedules->sum(fn (Schedule $schedule) => $schedule->availableBoats());
        $nextAvailableSchedule = $availableSchedules->first();

        if ($availableBoatCount <= 2) {
            return [
                'label' => 'Segera Habis',
                'variant' => 'warning',
                'note' => 'Tersisa '.$availableBoatCount.' kapal pada jadwal aktif yang masih bisa dipesan.',
                'remaining_slots' => $availableBoatCount,
                'next_start_at' => $nextAvailableSchedule?->start_at,
            ];
        }

        return [
            'label' => 'Tersedia',
            'variant' => 'success',
            'note' => 'Tersedia '.$availableBoatCount.' kapal pada jadwal aktif yang bisa dipesan.',
            'remaining_slots' => $availableBoatCount,
            'next_start_at' => $nextAvailableSchedule?->start_at,
        ];
    }

    public function destinationOptions()
    {
        return $this->relationLoaded('destinations')
            ? $this->destinations->sortBy('name')->values()
            : $this->destinations()->orderBy('name')->get();
    }

    public function defaultDestination(): ?Destination
    {
        return $this->destinationOptions()->first();
    }
}
