<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSavedCard extends Model
{
    use HasFactory;

    protected $table = 'user_saved_cards';

    protected $guarded = ['id'];

    protected $casts = [
        'is_default'       => 'boolean',
        'expiration_month' => 'integer',
        'expiration_year'  => 'integer',
    ];

    protected $hidden = [
        'mp_customer_id', // No exponer IDs internos de MP en la API
    ];

    /**
     * El usuario dueño de esta tarjeta.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Retorna el nombre de la marca para mostrar en UI.
     * Ej: "visa" → "Visa"
     */
    public function getBrandLabelAttribute(): string
    {
        return match(strtolower($this->payment_method_id)) {
            'visa'                    => 'Visa',
            'master', 'mastercard'   => 'Mastercard',
            'amex', 'american_express' => 'American Express',
            default                   => ucfirst($this->payment_method_id),
        };
    }

    /**
     * Expiration display: "05/28"
     */
    public function getExpirationDisplayAttribute(): string
    {
        return sprintf('%02d/%02d', $this->expiration_month, $this->expiration_year % 100);
    }
}
