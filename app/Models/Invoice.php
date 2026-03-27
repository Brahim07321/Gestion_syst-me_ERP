<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    
        protected $fillable = [
            'customer_id',
            'invoice_date',
            'due_date',
            'subtotal',
            'tax',
            'total',
            'invoice_number',
        ];
    

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }
}
