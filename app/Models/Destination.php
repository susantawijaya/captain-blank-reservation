<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Destination extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'image_path',
        'difficulty',
        'status',
    ];

    public function packages()
    {
        return $this->belongsToMany(SnorkelingPackage::class);
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }
}
