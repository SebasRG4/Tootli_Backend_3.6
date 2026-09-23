<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class CustomerBankAccount extends Model
{
    use HasFactory;

    protected $table = 'customer_bank_accounts';

    protected $fillable = [
        'user_id',
        'bank_code',
        'bank_name',
        'account_holder',
        'clabe_encrypted',
        'clabe_last4',
        'clabe_hash',
        'selfie_image',
        'cooling_off_until',
        'is_verified',
        'email_verified_at',
        'status',
    ];

    protected $hidden = [
        'clabe_encrypted',
        'clabe_hash',
    ];

    protected $casts = [
        'is_verified' => 'boolean',
        'cooling_off_until' => 'datetime',
        'email_verified_at' => 'datetime',
    ];

    protected $appends = [
        'masked_clabe',
        'is_in_cooling_off',
        'cooling_off_remaining_minutes',
        'selfie_image_full_url',
    ];

    /**
     * Get full URL for selfie image
     */
    public function getSelfieImageFullUrlAttribute(): ?string
    {
        if (empty($this->selfie_image)) {
            return null;
        }
        return asset('storage/app/public/customer/bank_accounts/selfies/' . $this->selfie_image);
    }

    /**
     * Get masked CLABE (e.g. •••• •••• •••• 1234)
     */
    public function getMaskedClabeAttribute(): string
    {
        return '•••• •••• •••• ' . $this->clabe_last4;
    }

    /**
     * Decrypt the full CLABE (bank-grade AES-256)
     */
    public function getDecryptedClabe(): ?string
    {
        try {
            return Crypt::decryptString($this->clabe_encrypted);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Check if account is within 24h cooling-off period
     */
    public function getIsInCoolingOffAttribute(): bool
    {
        return $this->cooling_off_until && $this->cooling_off_until->isFuture();
    }

    /**
     * Remaining cooling-off minutes
     */
    public function getCoolingOffRemainingMinutesAttribute(): int
    {
        if (!$this->is_in_cooling_off) {
            return 0;
        }
        return (int) now()->diffInMinutes($this->cooling_off_until, false);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function withdrawRequests()
    {
        return $this->hasMany(CustomerWithdrawRequest::class, 'customer_bank_account_id');
    }
}
