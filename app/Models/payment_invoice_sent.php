<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class payment_invoice_sent extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'paye',
        'payment_date',
    ];
}
