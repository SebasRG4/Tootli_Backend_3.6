<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryManCashDeposit extends Model
{
    protected $fillable = [
        'delivery_man_id',
        'amount',
        'photo',
        'latitude',
        'longitude',
        'status',
        'approved_by'
    ];

    public function delivery_man()
    {
        return $this->belongsTo(DeliveryMan::class);
    }

    public function approved_by_admin()
    {
        return $this->belongsTo(Admin::class, 'approved_by');
    }
}
