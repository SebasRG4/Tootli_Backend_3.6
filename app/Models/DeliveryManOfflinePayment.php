<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryManOfflinePayment extends Model
{
    use HasFactory;

    protected $table = 'delivery_man_offline_payments';

    protected $casts = [
        'delivery_man_id' => 'integer',
        'amount' => 'float',
        'method_id' => 'integer',
        'payment_info' => 'json',
    ];

    protected $guarded = ['id'];

    public function delivery_man()
    {
        return $this->belongsTo(DeliveryMan::class, 'delivery_man_id');
    }

    public function offline_payment_method()
    {
        return $this->belongsTo(OfflinePaymentMethod::class, 'method_id');
    }
}
