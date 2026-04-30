<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyProfile extends Model
{
    protected $fillable = [
        'name',
        'tagline',
        'description',
        'phone',
        'whatsapp',
        'email',
        'address',
        'bank_name',
        'bank_account_number',
        'bank_account_name',
        'instagram',
        'logo_path',
    ];
}
