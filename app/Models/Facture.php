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
        'paid_amount',
        'remaining_amount',
    ];

    public function items()
    {
        return $this->hasMany(FactureItem::class);
    }
    //relatin payment
    public function payments()
{
    return $this->hasMany(Payment::class);
}
//acc
public function getTotalPaidAttribute()
{
    return $this->payments()->sum('amount');
}

public function getRemainingToPayAttribute()
{
    return $this->total - $this->total_paid;
}

}