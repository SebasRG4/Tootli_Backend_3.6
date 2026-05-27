<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RepartidorPagoTiendaEfectivo extends Model
{
    use HasFactory;

    protected $table = 'repartidor_pagos_tienda_efectivo';

    protected $casts = [
        'order_id' => 'integer',
        'delivery_man_id' => 'integer',
        'store_id' => 'integer',
        'amount_paid' => 'float',
        'verified_by_store' => 'boolean',
    ];

    protected $fillable = [
        'order_id',
        'delivery_man_id',
        'store_id',
        'amount_paid',
        'verified_by_store',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function delivery_man()
    {
        return $this->belongsTo(DeliveryMan::class, 'delivery_man_id');
    }

    public function store()
    {
        return $this->belongsTo(Store::class, 'store_id');
    }
}
