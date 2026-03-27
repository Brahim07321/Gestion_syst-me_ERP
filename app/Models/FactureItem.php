<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FactureItem extends Model
{
    protected $fillable = [
        'facture_id',
        'referonce',
        'designation',
        'price',
        'quantity',
        'line_total',
    ];

    public function facture()
    {
        return $this->belongsTo(Facture::class);
    }
}