<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Donation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'transaction_ref',
        'amount',
        'payment_status',
        'payment_method'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
