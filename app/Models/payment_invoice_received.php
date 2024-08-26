<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class payment_invoice_received extends Model
{
    use HasFactory;

    protected $fillable = [
        'received_invoice_id',
        'paye',
        'payment_date',
    ];
}
