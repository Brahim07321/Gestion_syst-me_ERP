<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Facture extends Model
{
    protected $fillable = [
        'code_facture',
        'client_name',
        'total',
        'date_facture',
        'status',
    ];

    public function items()
    {
        return $this->hasMany(FactureItem::class);
    }
}