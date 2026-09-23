<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerWithdrawRequest extends Model
{
    use HasFactory;

    protected $table = 'customer_withdraw_requests';

    protected $fillable = [
        'user_id',
        'customer_bank_account_id',
        'amount',
        'fee',
        'net_amount',
        'status',
        'spei_tracking_key',
        'spei_proof_url',
        'idempotency_key',
        'audit_metadata',
        'rejection_reason',
        'processed_by',
        'processed_at',
    ];

    protected $casts = [
        'amount' => 'float',
        'fee' => 'float',
        'net_amount' => 'float',
        'audit_metadata' => 'array',
        'processed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function bankAccount()
    {
        return $this->belongsTo(CustomerBankAccount::class, 'customer_bank_account_id');
    }

    public function processor()
    {
        return $this->belongsTo(Admin::class, 'processed_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeTransferred($query)
    {
        return $query->where('status', 'transferred');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }
}
