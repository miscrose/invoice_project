<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class invoice extends Model
{
    use HasFactory;
    protected $fillable = ['date', 'due_date',  'client_id', 'status','company_id','type',];

 



  
}
