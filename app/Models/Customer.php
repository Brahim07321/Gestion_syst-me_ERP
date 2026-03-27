<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = ['name', 'address', 'contact'];

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
}
