<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [ 'company_id','name', 'address', 'contact'];

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
}
