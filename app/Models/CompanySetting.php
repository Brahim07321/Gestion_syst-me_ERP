<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanySetting extends Model
{
    protected $fillable = [
        'company_name',
        'city',
        'phone',
        'email',
        'address',
        'footer_note',
        'footer_contact',
        'logo',
    ];
}