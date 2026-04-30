<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'reservation_id',
        'amount',
        'method',
        'proof_image',
        'status',
        'verified_at',
        'notes',
    ];

    protected $casts = [
        'amount' => 'integer',
        'verified_at' => 'datetime',
    ];

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }
}
