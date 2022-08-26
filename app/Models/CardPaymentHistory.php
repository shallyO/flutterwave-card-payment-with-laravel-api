<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CardPaymentHistory extends Model
{
    use HasFactory;

    protected $table = 'card_payment_history';

    protected $fillable = ['card_number','flw_ref','status', 'customer_id','flw_id','system_ref'];

    protected $hidden = ['id', 'created_at', 'updated_at'];
}
