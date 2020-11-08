<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentInput extends Model
{
    protected $fillable = [
        'description_id','extra_info','amount','due_date','payment_date'
    ];

}
