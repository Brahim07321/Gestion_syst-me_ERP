<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $fillable = [
        'company_id',
        'name',
        'amount',
        'expense_date',
        'description',
    ];
}
