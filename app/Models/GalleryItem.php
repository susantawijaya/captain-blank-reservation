<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GalleryItem extends Model
{
    protected $fillable = [
        'title',
        'image_path',
        'category',
        'caption',
        'is_featured',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
    ];
}
