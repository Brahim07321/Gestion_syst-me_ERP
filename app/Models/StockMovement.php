<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    protected $fillable = [
        'product_id',
        'type',
        'quantity',
        'source',
        'reference',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
  



public function purchase()
{
    return $this->belongsTo(\App\Models\Purchase::class, 'reference', 'purchase_code')->withTrashed();
}


public function facture()
{
    return $this->belongsTo(Facture::class, 'reference', 'code_facture')->withTrashed();
}




}



