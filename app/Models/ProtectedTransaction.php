<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProtectedTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'buyer_id',
        'seller_id',
        'target_email_or_phone',
        'item_name',
        'amount',
        'protection_fee',
        'total_amount',
        'creator_role',
        'status',
        'auto_release_at',
        'payment_method',
    ];

    protected $casts = [
        'auto_release_at' => 'datetime',
    ];

    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function dispute()
    {
        return $this->morphOne(TootliDispute::class, 'disputable')->latestOfMany();
    }

    public function disputes()
    {
        return $this->morphMany(TootliDispute::class, 'disputable');
    }
}
