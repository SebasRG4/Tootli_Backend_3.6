<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DmCustomerCallAttempt extends Model
{
    protected $table = 'dm_customer_call_attempts';

    protected $guarded = ['id'];

    protected $casts = [
        'order_id' => 'integer',
        'delivery_man_id' => 'integer',
        'attempt_number' => 'integer',
        'confirmed_at_ms' => 'integer',
    ];
}
