<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $table = 'products';

    protected $fillable= [
       'Category_ID',
       'code',
       'Referonce',
       'Designation',
       'prace_bay',
       'prace_sell',
       'Quantite',

       
    ];
    

public function category()
{
    return $this->belongsTo(Category::class, 'Category_ID', 'id');
}
}
