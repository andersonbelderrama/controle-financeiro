<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentOutput extends Model
{
    protected $fillable = [
        'description_id','amount','due_date','payment_date'
    ];
}
