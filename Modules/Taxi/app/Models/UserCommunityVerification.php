<?php

namespace Modules\Taxi\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;
use App\Models\DeliveryMan;

class UserCommunityVerification extends Model
{
    use HasFactory;

    protected $table = 'user_community_verifications';

    protected $fillable = [
        'user_id',
        'delivery_man_id',
        'organization_id',
        'role',
        'institutional_email',
        'id_card_image',
        'verification_status',
        'rejection_reason',
        'verified_at',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function deliveryMan(): BelongsTo
    {
        return $this->belongsTo(DeliveryMan::class, 'delivery_man_id');
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(TaxiCommunityOrganization::class, 'organization_id');
    }

    public function isApproved(): bool
    {
        return $this->verification_status === 'approved';
    }
}
